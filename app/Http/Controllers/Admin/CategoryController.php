<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CategoryController extends CrudController
{
    protected string $model = Category::class;
    protected string $route = 'admin.categories';
    protected string $title = 'Kategorie';
    protected string $singular = 'kategorie';
    protected array $with = ['parent'];
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';
    protected ?string $slugFrom = 'name';

    protected function query() { return parent::query()->withCount('products'); }

    protected function columns(): array
    {
        return [
            'Kategorie' => fn ($c) => new HtmlString('<i class="bi '.e($c->icon).'"></i> '.($c->parent ? '<span class="text-secondary">'.e($c->parent->name).' › </span>' : '').'<b>'.e($c->name).'</b>'),
            'URL' => fn ($c) => '/katalog/'.$c->slug,
            'Produktů' => fn ($c) => $c->products_count,
            'Pořadí' => fn ($c) => $c->sort,
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:120', 'col' => 6],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:120', 'unique' => true, 'col' => 6],
            ['name' => 'parent_id', 'label' => 'Nadřazená kategorie', 'type' => 'select', 'rules' => 'nullable|exists:categories,id', 'options' => ['' => '– hlavní kategorie –'] + self::opts(Category::whereNull('parent_id')->when($item, fn ($q) => $q->where('id', '!=', $item->id))->orderBy('sort')->get()), 'col' => 4],
            ['name' => 'icon', 'label' => 'Ikona (Bootstrap Icons)', 'rules' => 'required|string|max:40', 'col' => 4, 'help' => 'např. bi-lightning-charge'],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 4],
            ['name' => 'description', 'label' => 'Popis (SEO text pod výpisem)', 'type' => 'textarea', 'col' => 12],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        if ($item->products()->exists()) return 'Kategorie obsahuje produkty – nejdřív je přesuň jinam.';
        if ($item->children()->exists()) return 'Kategorie má podkategorie.';
        return null;
    }
}
