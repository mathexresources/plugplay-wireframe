<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\SavedPlan;
use App\Models\Tool;
use App\Support\Cart;
use App\Support\CarContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::items();
        $summary = Cart::summary($items);
        $car = CarContext::model();
        $addMore = $car
            ? $car->fittingProductsQuery()->whereNotIn('id', $items->where('type', 'product')->pluck('model.id'))->orderByDesc('sold_count')->take(3)->get()
            : collect();
        return view('shop.cart', compact('items', 'summary', 'car', 'addMore'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id', 'qty' => 'nullable|integer|min:1|max:99',
            'variant' => 'nullable|string|max:60', 'with_tools' => 'nullable|array', 'with_tools.*' => 'integer|exists:products,id',
        ]);
        $product = Product::active()->findOrFail($data['product_id']);
        $variant = $data['variant'] ?? null;
        if ($product->variantList() && ! in_array($variant, $product->variantList(), true)) {
            $variant = $product->variantList()[0];
        }
        Cart::add('product', $product->id, $data['qty'] ?? 1, $variant);
        foreach ($data['with_tools'] ?? [] as $toolProductId) Cart::add('product', (int) $toolProductId);

        $msg = $product->name.' je v plánu.';
        return $request->boolean('stay') ? back()->with('ok', $msg) : redirect()->route('cart')->with('ok', $msg);
    }

    public function addBundle(Bundle $bundle)
    {
        abort_unless($bundle->is_active, 404);
        Cart::add('bundle', $bundle->id);
        return redirect()->route('cart')->with('ok', 'Sada „'.$bundle->name.'“ je v plánu.');
    }

    public function addTools(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('tools', []));
        $added = 0;
        foreach (Tool::whereIn('id', $ids)->whereNotNull('product_id')->get() as $tool) {
            Cart::add('product', $tool->product_id); $added++;
        }
        return back()->with('ok', $added ? 'Chybějící nářadí jsme přidali do plánu.' : 'Nevybral jsi žádné nářadí, které u nás koupíš.');
    }

    public function update(Request $request)
    {
        $request->validate(['key' => 'required|string', 'qty' => 'required|integer|min:0|max:99']);
        Cart::update($request->input('key'), $request->integer('qty'));
        return back();
    }

    public function coupon(Request $request)
    {
        if ($request->boolean('remove')) {
            session()->forget('coupon');
            return back()->with('ok', 'Slevový kód jsme odebrali.');
        }
        $code = Str::upper(trim((string) $request->input('code')));
        $coupon = Coupon::where('code', $code)->first();
        $problem = $coupon ? $coupon->problemFor(Cart::summary()->subtotal) : 'Tento slevový kód neexistuje.';
        if ($problem) return back()->withErrors(['code' => $problem])->withInput();
        session(['coupon' => $coupon->code]);
        return back()->with('ok', "Kód {$coupon->code} uplatněn ({$coupon->label}).");
    }

    public function save(Request $request)
    {
        $lines = array_values(Cart::raw());
        if (! $lines) return back()->withErrors(['plan' => 'Plán je prázdný.']);
        $car = CarContext::model();
        $plan = SavedPlan::create([
            'user_id' => auth()->id(), 'car_model_id' => $car?->id, 'token' => Str::random(40), 'items' => $lines,
            'name' => $request->input('name') ?: (($car?->short_name ?? 'Můj plán').' – '.now()->format('j. n. Y')),
        ]);
        if ($request->boolean('share')) {
            return back()->with('share_url', route('cart.shared', $plan->token));
        }
        return auth()->check()
            ? redirect()->route('account.plans')->with('ok', 'Plán je uložený v tvém účtu.')
            : back()->with('share_url', route('cart.shared', $plan->token))->with('ok', 'Plán jsme uložili. Odkaz si zkopíruj, ať se k němu můžeš vrátit.');
    }

    public function shared(string $token)
    {
        $plan = SavedPlan::where('token', $token)->with('carModel')->firstOrFail();
        $ids = collect($plan->items);
        $products = Product::active()->whereIn('id', $ids->where('type', 'product')->pluck('id'))->get()->keyBy('id');
        $bundles = Bundle::whereIn('id', $ids->where('type', 'bundle')->pluck('id'))->get()->keyBy('id');
        $lines = $ids->map(fn ($l) => (object) ['line' => $l, 'model' => $l['type'] === 'bundle' ? $bundles->get($l['id']) : $products->get($l['id'])])
            ->filter(fn ($l) => $l->model);
        return view('shop.shared-plan', compact('plan', 'lines'));
    }

    public function loadShared(string $token)
    {
        $plan = SavedPlan::where('token', $token)->firstOrFail();
        foreach ($plan->items as $l) Cart::add($l['type'], (int) $l['id'], (int) $l['qty'], $l['variant'] ?? null);
        if ($plan->carModel) CarContext::set($plan->carModel);
        return redirect()->route('cart')->with('ok', 'Plán „'.$plan->name.'“ jsme nahráli.');
    }
}
