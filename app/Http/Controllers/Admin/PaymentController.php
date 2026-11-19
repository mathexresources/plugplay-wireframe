<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/** Payment types are fixed by code (card → paywall dead end, transfer → QR, cod); only labels/prices are editable. */
class PaymentController extends CrudController
{
    protected string $model = PaymentMethod::class;
    protected string $route = 'admin.payments';
    protected string $title = 'Platby';
    protected string $singular = 'platba';
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';

    protected function columns(): array
    {
        return [
            'Platba' => fn ($p) => new HtmlString('<i class="bi '.e($p->icon).'"></i> <b>'.e($p->name).'</b> <code class="small">'.$p->code.'</code>'),
            'Příplatek' => fn ($p) => $p->price ? czk($p->price) : '–',
            'Stav' => fn ($p) => new HtmlString($p->is_active ? '<span class="badge text-bg-success">aktivní</span>' : '<span class="badge text-bg-secondary">vypnutá</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'icon', 'label' => 'Ikona', 'rules' => 'required|string|max:40', 'col' => 2],
            ['name' => 'price', 'label' => 'Příplatek (Kč)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 2],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 2],
            ['name' => 'is_active', 'label' => 'Aktivní', 'type' => 'checkbox', 'col' => 3],
        ];
    }
}
