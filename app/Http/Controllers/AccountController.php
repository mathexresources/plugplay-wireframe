<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarUpgrade;
use App\Models\Guide;
use App\Models\GuideProgress;
use App\Models\Order;
use App\Models\SavedPlan;
use App\Support\Cart;
use App\Support\CarContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    private function own($model): void { abort_unless($model->user_id === auth()->id(), 404); }

    public function garage()
    {
        $cars = auth()->user()->cars()->with(['carModel', 'upgrades.product'])->get();
        foreach ($cars as $car) $car->total_upgrades = $car->carModel->fittingProductsQuery()->count();
        $models = CarModel::orderBy('brand')->orderBy('name')->get();
        $guideCounts = $this->guidesForUser()->groupBy(fn ($g) => $g->car_model_id)->map->count();
        return view('account.garage', compact('cars', 'models', 'guideCounts'));
    }

    private function carRules(): array
    {
        return ['car_model_id' => 'required|exists:car_models,id', 'nickname' => 'nullable|string|max:60',
            'year' => 'nullable|integer|between:1950,'.(now()->year + 1), 'body_type' => 'nullable|string|max:40',
            'engine' => 'nullable|string|max:60', 'radio' => 'nullable|string|max:60', 'vin' => 'nullable|alpha_num|size:17',
            'photo' => 'nullable|image|max:4096'];
    }

    public function storeCar(Request $request)
    {
        $data = $request->validate($this->carRules());
        unset($data['photo']);
        $car = auth()->user()->cars()->create($data);
        if ($request->hasFile('photo')) $car->update(['photo' => $request->file('photo')->store('cars', 'public')]);
        if ($request->boolean('make_active') || auth()->user()->cars()->count() === 1) CarContext::activate($car);
        return back()->with('ok', 'Auto je v garáži.');
    }

    public function updateCar(Request $request, Car $car)
    {
        $this->own($car);
        $data = $request->validate($this->carRules());
        unset($data['photo']);
        if ($request->hasFile('photo')) {
            if ($car->photo) Storage::disk('public')->delete($car->photo);
            $data['photo'] = $request->file('photo')->store('cars', 'public');
        }
        $car->update($data);
        if ($car->is_active) CarContext::set($car->carModel, $car->body_type);
        return back()->with('ok', 'Auto je upravené.');
    }

    public function deleteCar(Car $car)
    {
        $this->own($car);
        if ($car->photo) Storage::disk('public')->delete($car->photo);
        $wasActive = $car->is_active;
        $car->delete();
        if ($wasActive) {
            $next = auth()->user()->cars()->first();
            $next ? CarContext::activate($next) : CarContext::set(null);
        }
        return back()->with('ok', 'Auto jsme odebrali z garáže.');
    }

    public function activateCar(Car $car)
    {
        $this->own($car);
        CarContext::activate($car);
        return back()->with('ok', "{$car->label} je teď aktivní auto. Web ti ukazuje produkty pro něj.");
    }

    public function toggleUpgrade(Car $car, CarUpgrade $upgrade)
    {
        $this->own($car);
        abort_unless($upgrade->car_id === $car->id, 404);
        $upgrade->update(['installed' => ! $upgrade->installed]);
        return back();
    }

    public function orders()
    {
        $orders = auth()->user()->orders()->withCount('items')->get();
        return view('account.orders', compact('orders'));
    }

    public function order(Order $order)
    {
        $this->own($order);
        $order->load('items.product');
        return view('account.order', compact('order'));
    }

    private function guidesForUser()
    {
        $user = auth()->user();
        $productIds = $user->orders()->with('items')->get()->flatMap->items->pluck('product_id')->filter()->unique();
        $models = $user->cars()->pluck('car_model_id');
        return Guide::with(['product', 'carModel'])->where('is_published', true)->whereIn('product_id', $productIds)
            ->get()->sortBy(fn ($g) => $models->contains($g->car_model_id) ? 0 : 1)
            ->unique('product_id')->values();
    }

    public function guides(Request $request)
    {
        $guides = $this->guidesForUser();
        $cars = auth()->user()->cars()->with('carModel')->get();
        if ($request->filled('auto')) $guides = $guides->where('car_model_id', (int) $request->query('auto'));
        $progress = GuideProgress::where('user_id', auth()->id())->get()->keyBy('guide_id');
        return view('account.guides', compact('guides', 'cars', 'progress'));
    }

    public function plans()
    {
        $plans = auth()->user()->plans()->with('carModel')->get();
        return view('account.plans', compact('plans'));
    }

    public function openPlan(SavedPlan $plan)
    {
        $this->own($plan);
        Cart::save([]);
        foreach ($plan->items as $l) Cart::add($l['type'], (int) $l['id'], (int) $l['qty'], $l['variant'] ?? null);
        if ($plan->carModel) CarContext::set($plan->carModel);
        return redirect()->route('cart')->with('ok', 'Plán „'.$plan->name.'“ je otevřený.');
    }

    public function deletePlan(SavedPlan $plan)
    {
        $this->own($plan);
        $plan->delete();
        return back()->with('ok', 'Plán jsme smazali.');
    }

    public function profile()
    {
        $user = auth()->user()->load('addresses');
        return view('account.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'required|email|max:120|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
        ]);
        $user->update($data + ['notify_upgrades' => $request->boolean('notify_upgrades'), 'notify_deals' => $request->boolean('notify_deals')]);
        return back()->with('ok', 'Údaje jsou uložené.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate(['current_password' => 'required|current_password', 'password' => 'required|min:8|confirmed'],
            ['current_password.current_password' => 'Současné heslo nesedí.']);
        auth()->user()->update(['password' => $request->input('password')]);
        return back()->with('ok', 'Heslo je změněné.');
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate(['label' => 'nullable|string|max:40', 'street' => 'required|string|max:120',
            'city' => 'required|string|max:80', 'zip' => 'required|string|max:10']);
        $user = auth()->user();
        if ($request->boolean('is_default') || ! $user->addresses()->exists()) {
            $user->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        $user->addresses()->create($data);
        return back()->with('ok', 'Adresa je uložená.');
    }

    public function deleteAddress(Address $address)
    {
        $this->own($address);
        $address->delete();
        return back()->with('ok', 'Adresu jsme smazali.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => 'required|current_password'], ['password.current_password' => 'Heslo nesedí.']);
        $user = auth()->user();
        abort_if($user->is_admin, 403, 'Správce nemůže zrušit vlastní účet.');
        Auth::logout();
        $user->delete(); // orders stay (user_id → null) for accounting
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('ok', 'Účet jsme zrušili. Mrzí nás, že odcházíš.');
    }
}
