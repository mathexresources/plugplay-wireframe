<?php

namespace App\Http\Controllers\Admin;

use App\Models\CarModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CarModelController extends CrudController
{
    protected string $model = CarModel::class;
    protected string $route = 'admin.cars';
    protected string $title = 'Modely aut';
    protected string $singular = 'model auta';
    protected array $search = ['brand', 'name', 'code'];
    protected string $orderBy = 'sort';
    protected string $orderDir = 'asc';

    protected function query() { return parent::query()->withCount('products'); }

    protected function columns(): array
    {
        return [
            'Model' => fn ($m) => new HtmlString('<b>'.e($m->full_name).'</b>'.($m->is_popular ? ' <span class="badge text-bg-primary">oblíbený</span>' : '')),
            'Roky' => fn ($m) => $m->years,
            'Karoserie' => fn ($m) => $m->body_types,
            'Ověřených produktů' => fn ($m) => $m->products_count,
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'brand', 'label' => 'Značka', 'rules' => 'required|string|max:60', 'col' => 4],
            ['name' => 'name', 'label' => 'Model', 'rules' => 'required|string|max:60', 'col' => 4],
            ['name' => 'code', 'label' => 'Kód generace', 'rules' => 'nullable|string|max:20', 'col' => 4, 'help' => 'např. E46, B6'],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:80', 'unique' => true, 'col' => 4, 'help' => 'Prázdné = vytvoří se samo'],
            ['name' => 'year_from', 'label' => 'Vyráběno od', 'type' => 'number', 'rules' => 'nullable|integer|min:1900|max:2100', 'col' => 2],
            ['name' => 'year_to', 'label' => 'do', 'type' => 'number', 'rules' => 'nullable|integer|min:1900|max:2100', 'col' => 2],
            ['name' => 'sort', 'label' => 'Pořadí', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 2],
            ['name' => 'is_popular', 'label' => 'Oblíbený (úvodní stránka)', 'type' => 'checkbox', 'col' => 2],
            ['name' => 'body_types', 'label' => 'Karoserie (oddělené čárkou)', 'rules' => 'nullable|string|max:200', 'col' => 12],
            ['name' => 'description', 'label' => 'SEO text landing page modelu', 'type' => 'textarea', 'col' => 12],
        ];
    }

    protected function save(Request $request, Model $item): void
    {
        if (! $request->filled('slug')) $request->merge(['slug' => Str::slug($request->input('brand').' '.($request->input('code') ?: $request->input('name')))]);
        parent::save($request, $item);
    }
}
