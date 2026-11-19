<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Model;

class ToolController extends CrudController
{
    protected string $model = Tool::class;
    protected string $route = 'admin.tools';
    protected string $title = 'Nářadí';
    protected string $singular = 'nářadí';
    protected array $with = ['product'];
    protected string $orderBy = 'name';
    protected string $orderDir = 'asc';

    protected function query() { return parent::query()->withCount('products'); }

    protected function columns(): array
    {
        return [
            'Nářadí' => fn ($t) => $t->name,
            'Prodáváme jako' => fn ($t) => $t->product ? $t->product->name.' ('.czk($t->product->price).')' : '– běžně doma –',
            'Potřeba u produktů' => fn ($t) => $t->products_count,
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'product_id', 'label' => 'Produkt v e-shopu (pokud ho prodáváme)', 'type' => 'select', 'rules' => 'nullable|exists:products,id', 'options' => ['' => '– neprodáváme –'] + self::opts(Product::orderBy('name')->get()), 'col' => 6],
        ];
    }
}
