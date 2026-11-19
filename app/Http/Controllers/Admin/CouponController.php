<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CouponController extends CrudController
{
    protected string $model = Coupon::class;
    protected string $route = 'admin.coupons';
    protected string $title = 'Slevové kódy';
    protected string $singular = 'slevový kód';
    protected array $search = ['code'];

    protected function columns(): array
    {
        return [
            'Kód' => fn ($c) => new HtmlString('<code class="fs-6">'.e($c->code).'</code>'),
            'Sleva' => fn ($c) => $c->label,
            'Od částky' => fn ($c) => $c->min_total ? czk($c->min_total) : '–',
            'Použito' => fn ($c) => $c->used.($c->usage_limit ? ' / '.$c->usage_limit : ''),
            'Platnost' => fn ($c) => new HtmlString(! $c->is_active ? '<span class="badge text-bg-secondary">vypnutý</span>' : ($c->valid_until ? 'do '.$c->valid_until->format('j. n. Y') : 'neomezeně')),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'code', 'label' => 'Kód', 'rules' => 'required|alpha_dash|max:40', 'unique' => true, 'col' => 4],
            ['name' => 'type', 'label' => 'Typ', 'type' => 'select', 'rules' => 'required|in:percent,fixed', 'options' => ['percent' => 'Procenta', 'fixed' => 'Pevná částka (Kč)'], 'col' => 4],
            ['name' => 'value', 'label' => 'Hodnota', 'type' => 'number', 'rules' => 'required|integer|min:1', 'col' => 4],
            ['name' => 'min_total', 'label' => 'Minimální nákup (Kč)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'usage_limit', 'label' => 'Max. počet použití', 'type' => 'number', 'rules' => 'nullable|integer|min:1', 'col' => 3],
            ['name' => 'valid_until', 'label' => 'Platí do', 'type' => 'date', 'rules' => 'nullable|date', 'col' => 3],
            ['name' => 'is_active', 'label' => 'Aktivní', 'type' => 'checkbox', 'default' => true, 'col' => 3],
        ];
    }

    protected function save(Request $request, Model $item): void
    {
        $request->merge(['code' => Str::upper((string) $request->input('code'))]);
        if ($request->input('type') === 'percent') $request->validate(['value' => 'max:100']);
        parent::save($request, $item);
    }
}
