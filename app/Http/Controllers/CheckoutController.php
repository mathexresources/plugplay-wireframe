<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarUpgrade;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Support\Cart;
use App\Support\CarContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show()
    {
        $items = Cart::items();
        if ($items->isEmpty()) return redirect()->route('cart')->withErrors(['plan' => 'Plán je zatím prázdný.']);
        $summary = Cart::summary($items);
        $shipping = ShippingMethod::where('is_active', true)->orderBy('sort')->get();
        $payments = PaymentMethod::where('is_active', true)->orderBy('sort')->get();
        $user = auth()->user();
        $address = $user?->addresses->first();
        return view('shop.checkout', compact('items', 'summary', 'shipping', 'payments', 'user', 'address') + ['car' => CarContext::model()]);
    }

    public function store(Request $request)
    {
        $items = Cart::items();
        if ($items->isEmpty()) return redirect()->route('cart');

        $ship = ShippingMethod::where('is_active', true)->find($request->integer('shipping_method_id'));
        $pay = PaymentMethod::where('is_active', true)->find($request->integer('payment_method_id'));
        $guest = ! auth()->check();

        $data = $request->validate([
            'email' => 'required|email|max:120', 'phone' => 'required|string|max:30', 'name' => 'required|string|max:120',
            'shipping_method_id' => 'required|exists:shipping_methods,id', 'payment_method_id' => 'required|exists:payment_methods,id',
            'street' => [$ship?->needs_address ? 'required' : 'nullable', 'string', 'max:120'],
            'city' => [$ship?->needs_address ? 'required' : 'nullable', 'string', 'max:80'],
            'zip' => [$ship?->needs_address ? 'required' : 'nullable', 'string', 'max:10'],
            'pickup_point' => [$ship?->needs_pickup_point ? 'required' : 'nullable', 'string', 'max:160'],
            'note' => 'nullable|string|max:1000',
            'terms' => 'accepted',
            'create_account' => 'nullable|boolean',
            'password' => [$guest && $request->boolean('create_account') ? 'required' : 'nullable', 'nullable', 'string', 'min:8'],
        ], ['terms.accepted' => 'Bez souhlasu s obchodními podmínkami objednávku nedokončíme.',
            'pickup_point.required' => 'Vyber výdejní místo Zásilkovny.']);

        if ($guest && $request->boolean('create_account') && User::where('email', $data['email'])->exists()) {
            return back()->withInput()->withErrors(['email' => 'Účet s tímto e-mailem už existuje – přihlas se, nebo odškrtni založení účtu.']);
        }

        $summary = Cart::summary($items);
        $shippingPrice = ($ship->free_over_threshold && $summary->freeShipping) ? 0 : $ship->price;
        $car = CarContext::model();

        $order = DB::transaction(function () use ($data, $items, $summary, $ship, $pay, $shippingPrice, $car, $guest, $request) {
            $user = auth()->user();
            if ($guest && $request->boolean('create_account')) {
                $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'password' => $data['password']]);
                Auth::login($user);
            }
            $order = Order::create([
                'number' => now()->year.'-'.str_pad((string) ((Order::max('id') ?? 0) + 101), 4, '0', STR_PAD_LEFT),
                'token' => Str::random(40), 'user_id' => $user?->id,
                'car_model_id' => $car?->id, 'car_label' => $car?->short_name,
                'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'],
                'street' => $ship->needs_address ? $data['street'] : null, 'city' => $ship->needs_address ? $data['city'] : null,
                'zip' => $ship->needs_address ? $data['zip'] : null, 'pickup_point' => $ship->needs_pickup_point ? $data['pickup_point'] : null,
                'shipping_name' => $ship->name, 'shipping_price' => $shippingPrice,
                'payment_code' => $pay->code, 'payment_name' => $pay->name, 'payment_price' => $pay->price,
                'coupon_code' => $summary->coupon?->code, 'discount' => $summary->discount,
                'subtotal' => $summary->subtotal, 'total' => $summary->total + $shippingPrice + $pay->price,
                'note' => $data['note'] ?? null,
            ]);

            $productIds = [];
            foreach ($items as $i) {
                $order->items()->create([
                    'product_id' => $i->type === 'product' ? $i->model->id : null,
                    'bundle_id' => $i->type === 'bundle' ? $i->model->id : null,
                    'name' => $i->type === 'bundle' ? 'Sada: '.$i->model->name : $i->model->name,
                    'variant' => $i->variant, 'price' => $i->price, 'quantity' => $i->qty,
                ]);
                $products = $i->type === 'bundle' ? $i->model->products : collect([$i->model]);
                foreach ($products as $p) {
                    $fresh = Product::lockForUpdate()->find($p->id);
                    $fresh?->update(['stock' => max(0, $fresh->stock - $i->qty), 'sold_count' => $fresh->sold_count + $i->qty]);
                    $productIds[] = $p->id;
                }
            }
            if ($summary->coupon) Coupon::whereKey($summary->coupon->id)->increment('used');

            // remember what goes into which car (garage "čeká na montáž")
            if ($user && $car) {
                $garageCar = $user->cars()->where('car_model_id', $car->id)->first()
                    ?? Car::create(['user_id' => $user->id, 'car_model_id' => $car->id, 'body_type' => CarContext::body(), 'is_active' => ! $user->cars()->exists()]);
                foreach (array_unique($productIds) as $pid) {
                    $prod = Product::find($pid);
                    if ($prod && $prod->category?->slug !== 'naradi') CarUpgrade::firstOrCreate(['car_id' => $garageCar->id, 'product_id' => $pid]);
                }
            }
            return $order;
        });

        $this->sendConfirmation($order);
        Cart::clear();

        return $order->payment_code === 'card'
            ? redirect()->route('checkout.gateway', ['order' => $order, 't' => $order->token])
            : redirect()->route('checkout.done', ['order' => $order, 't' => $order->token]);
    }

    /** Card payment: school project → dead end instead of a real payment gateway. */
    public function gateway(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        return view('shop.gateway', compact('order'));
    }

    public function done(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->load('items.product.guides', 'carModel');
        $guides = $order->items->map(fn ($i) => $i->product?->guideFor($order->carModel))->filter()->unique('id');
        $tools = $order->items->flatMap(fn ($i) => $i->product?->tools ?? collect())->unique('id');
        $boughtToolProducts = $order->items->pluck('product_id')->filter()->all();
        return view('shop.done', compact('order', 'guides', 'tools', 'boughtToolProducts') + [
            'bank' => Setting::get('bank_account'), 'iban' => Setting::get('iban'), 'shopName' => Setting::get('shop_name'),
        ]);
    }

    public function createAccount(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        abort_if($order->user_id, 403);
        $data = $request->validate(['password' => 'required|string|min:8']);
        if (User::where('email', $order->email)->exists()) {
            return back()->withErrors(['password' => 'Účet s e-mailem '.$order->email.' už existuje. Přihlas se.']);
        }
        $user = User::create(['name' => $order->name, 'email' => $order->email, 'phone' => $order->phone, 'password' => $data['password']]);
        $order->update(['user_id' => $user->id]);
        if ($order->car_model_id) {
            $car = Car::create(['user_id' => $user->id, 'car_model_id' => $order->car_model_id, 'is_active' => true]);
            foreach ($order->items as $i) if ($i->product_id) CarUpgrade::firstOrCreate(['car_id' => $car->id, 'product_id' => $i->product_id]);
        }
        Auth::login($user);
        return redirect()->route('account.garage')->with('ok', 'Účet je založený a auto i objednávka jsou uložené.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $ok = hash_equals($order->token, (string) $request->query('t', $request->input('t')))
            || (auth()->check() && (auth()->id() === $order->user_id || auth()->user()->is_admin));
        abort_unless($ok, 404);
    }

    private function sendConfirmation(Order $order): void
    {
        $order->load('items');
        $lines = $order->items->map(fn ($i) => "- {$i->quantity}× {$i->name}".($i->variant ? " ({$i->variant})" : '').' – '.czk($i->price * $i->quantity))->implode("\n");
        $body = "Díky za objednávku #{$order->number}!\n\n{$lines}\n\nDoprava: {$order->shipping_name} ".czk($order->shipping_price)
            ."\nPlatba: {$order->payment_name}\nCelkem: ".czk($order->total)
            ."\n\nStav objednávky: ".route('checkout.done', ['order' => $order, 't' => $order->token])
            ."\n\n(Školní projekt – objednávka není závazná.)";
        try {
            Mail::raw($body, fn ($m) => $m->to($order->email)->subject("Objednávka #{$order->number} – PlugPlay.cz"));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
