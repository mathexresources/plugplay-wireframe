<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\User;
use App\Support\CarContext;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function form()
    {
        $models = CarModel::orderBy('brand')->orderBy('name')->get();
        return view('shop.login', compact('models'));
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'E-mail nebo heslo nesedí.'])->onlyInput('email');
        }
        $cart = session('cart'); $coupon = session('coupon');
        $request->session()->regenerate();
        if ($cart) session(['cart' => $cart]); // keep the plan across login
        if ($coupon) session(['coupon' => $coupon]);
        if ($active = Auth::user()->activeCar) CarContext::set($active->carModel, $active->body_type);
        return redirect()->intended(Auth::user()->is_admin ? route('admin.dashboard') : route('account.garage'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email',
            'password' => 'required|string|min:8',
            'car_model_id' => 'nullable|exists:car_models,id',
            'body_type' => 'nullable|string|max:40',
        ], ['email.unique' => 'Účet s tímto e-mailem už existuje.'], ['password' => 'heslo']);

        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
            'notify_upgrades' => $request->boolean('newsletter')]);
        $carModelId = $data['car_model_id'] ?? CarContext::model()?->id;
        if ($carModelId) {
            $car = Car::create(['user_id' => $user->id, 'car_model_id' => $carModelId, 'body_type' => $data['body_type'] ?? null, 'is_active' => true]);
            CarContext::set($car->carModel, $car->body_type);
        }
        Auth::login($user);
        return redirect()->route('account.garage')->with('ok', 'Vítej v PlugPlay! Účet je založený.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function forgotForm() { return view('shop.forgot'); }

    public function forgotSend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));
        // Same answer whether the account exists or not (no account enumeration)
        return back()->with('ok', 'Pokud účet existuje, poslali jsme na e-mail odkaz pro nastavení nového hesla.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('shop.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => 'required|min:8|confirmed']);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('ok', 'Heslo je změněné, můžeš se přihlásit.')
            : back()->withErrors(['email' => 'Odkaz pro změnu hesla je neplatný nebo vypršel.']);
    }
}
