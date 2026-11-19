<?php

namespace App\Http\Controllers\Admin;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ShippingController extends CrudController
{
    protected string $model = ShippingMethod::class;
    protected string $route = 'admin.shipping';
    protected string $title = 'Doprava';
    protected string $singular = 'způsob dopravy';
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';

    protected function columns(): array
    {
        return [
            'Doprava' => fn ($s) => new HtmlString('<i class="bi '.e($s->icon).'"></i> <b>'.e($s->name).'</b><div class="small text-secondary">'.e($s->description).'</div>'),
            'Cena' => fn ($s) => $s->price ? czk($s->price) : 'zdarma',
            'Zdarma nad limit' => fn ($s) => $s->free_over_threshold ? 'ano' : 'ne',
            'Stav' => fn ($s) => new HtmlString($s->is_active ? '<span class="badge text-bg-success">aktivní</span>' : '<span class="badge text-bg-secondary">vypnutá</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'icon', 'label' => 'Ikona', 'rules' => 'required|string|max:40', 'col' => 3],
            ['name' => 'price', 'label' => 'Cena (Kč)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'description', 'label' => 'Popis', 'rules' => 'nullable|string|max:255', 'col' => 9],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'needs_pickup_point', 'label' => 'Vyžaduje výdejní místo', 'type' => 'checkbox', 'col' => 3],
            ['name' => 'needs_address', 'label' => 'Vyžaduje adresu', 'type' => 'checkbox', 'col' => 3],
            ['name' => 'free_over_threshold', 'label' => 'Zdarma nad limit', 'type' => 'checkbox', 'default' => true, 'col' => 3],
            ['name' => 'is_active', 'label' => 'Aktivní', 'type' => 'checkbox', 'default' => true, 'col' => 3],
        ];
    }
}
