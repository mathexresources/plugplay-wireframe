<?php

namespace App\Http\Controllers\Admin;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ZoneController extends CrudController
{
    protected string $model = Zone::class;
    protected string $route = 'admin.zones';
    protected string $title = 'Zóny auta';
    protected string $singular = 'zóna';
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';
    protected ?string $slugFrom = 'name';

    protected function query() { return parent::query()->withCount('products'); }

    protected function columns(): array
    {
        return [
            'Zóna' => fn ($z) => new HtmlString('<i class="bi '.e($z->icon).'"></i> <b>'.e($z->name).'</b>'),
            'Pozice na schématu' => fn ($z) => "{$z->pos_x} % × {$z->pos_y} %",
            'Produktů' => fn ($z) => $z->products_count,
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:80', 'col' => 6],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:80', 'unique' => true, 'col' => 6],
            ['name' => 'icon', 'label' => 'Ikona', 'rules' => 'required|string|max:40', 'col' => 3],
            ['name' => 'pos_x', 'label' => 'Pozice zleva (%)', 'type' => 'number', 'rules' => 'required|integer|between:0,100', 'col' => 3],
            ['name' => 'pos_y', 'label' => 'Pozice shora (%)', 'type' => 'number', 'rules' => 'required|integer|between:0,100', 'col' => 3],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
        ];
    }
}
