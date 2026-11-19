<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bundle;
use App\Models\CarModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class BundleController extends CrudController
{
    protected string $model = Bundle::class;
    protected string $route = 'admin.bundles';
    protected string $title = 'Sady';
    protected string $singular = 'sada';
    protected array $with = ['carModel', 'products'];
    protected ?string $slugFrom = 'name';

    protected function columns(): array
    {
        return [
            'Sada' => fn ($b) => new HtmlString('<b>'.e($b->name).'</b><div class="small text-secondary">'.e($b->products->pluck('name')->implode(' · ')).'</div>'),
            'Auto' => fn ($b) => $b->carModel?->short_name ?? '–',
            'Cena' => fn ($b) => new HtmlString(czk($b->price).' <s class="small text-secondary">'.czk($b->full_price).'</s>'),
            'Stav' => fn ($b) => new HtmlString($b->is_active ? '<span class="badge text-bg-success">aktivní</span>' : '<span class="badge text-bg-secondary">skrytá</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:120', 'unique' => true, 'col' => 6],
            ['name' => 'car_model_id', 'label' => 'Pro auto', 'type' => 'select', 'rules' => 'nullable|exists:car_models,id', 'options' => ['' => '–'] + self::opts(CarModel::orderBy('brand')->get(), 'full_name'), 'col' => 4],
            ['name' => 'price', 'label' => 'Cena sady (Kč)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 4],
            ['name' => 'is_active', 'label' => 'Aktivní', 'type' => 'checkbox', 'default' => true, 'col' => 4],
            ['name' => 'description', 'label' => 'Popis', 'type' => 'textarea', 'col' => 12],
            ['name' => 'products', 'label' => 'Produkty v sadě', 'type' => 'multiselect', 'options' => self::opts(Product::orderBy('name')->get(), fn ($p) => $p->name.' ('.czk($p->price).')'), 'value' => $item?->products->pluck('id')->all() ?? [], 'col' => 12],
        ];
    }
}
