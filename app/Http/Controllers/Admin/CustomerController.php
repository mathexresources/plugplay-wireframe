<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = User::withCount('orders')->withSum('orders', 'total')->latest();
        if ($t = trim((string) $request->query('q'))) $q->where(fn ($w) => $w->where('name', 'like', "%$t%")->orWhere('email', 'like', "%$t%"));
        if ($request->query('role') === 'admin') $q->where('is_admin', true);
        $users = $q->paginate(25)->withQueryString();
        return view('admin.customers.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['orders', 'cars.carModel', 'addresses', 'plans']);
        return view('admin.customers.show', compact('user'));
    }

    public function toggleAdmin(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Sám sobě práva správce neodebereš.');
        $user->forceFill(['is_admin' => ! $user->is_admin])->save();
        return back()->with('ok', $user->is_admin ? "{$user->name} je teď správce." : "{$user->name} už není správce.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Nemůžeš smazat sám sebe.');
        $user->delete();
        return redirect()->route('admin.customers.index')->with('ok', 'Zákazník je smazaný (objednávky zůstaly).');
    }
}
