<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const FIELDS = [
        'shop_name' => ['Název obchodu', 'required|string|max:80'],
        'shop_email' => ['Kontaktní e-mail', 'required|email'],
        'shop_phone' => ['Telefon', 'nullable|string|max:30'],
        'shop_hours' => ['Provozní doba', 'nullable|string|max:80'],
        'free_shipping_from' => ['Doprava zdarma od (Kč, 0 = nikdy)', 'required|integer|min:0'],
        'bank_account' => ['Číslo účtu (QR platba)', 'required|string|max:40'],
        'iban' => ['IBAN (QR platba)', 'required|string|max:34'],
        'company' => ['Provozovatel', 'nullable|string|max:120'],
    ];

    public function edit()
    {
        $values = collect(self::FIELDS)->map(fn ($f, $k) => Setting::get($k));
        return view('admin.settings', ['fields' => self::FIELDS, 'values' => $values]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(collect(self::FIELDS)->map(fn ($f) => $f[1])->all());
        foreach ($data as $k => $v) Setting::put($k, $v);
        return back()->with('ok', 'Nastavení je uložené.');
    }
}
