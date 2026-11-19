<?php

namespace App\Http\Controllers\Admin;

use App\Models\CarModel;
use App\Models\Guide;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class GuideController extends CrudController
{
    protected string $model = Guide::class;
    protected string $route = 'admin.guides';
    protected string $title = 'Návody';
    protected string $singular = 'návod';
    protected array $search = ['title'];
    protected array $with = ['product', 'carModel'];
    protected ?string $slugFrom = 'title';

    protected function columns(): array
    {
        return [
            'Návod' => fn ($g) => new HtmlString('<b>'.e($g->title).'</b><div class="small text-secondary">'.e($g->product?->name).'</div>'),
            'Auto' => fn ($g) => $g->carModel?->short_name ?? 'obecný',
            'Kroků' => fn ($g) => $g->step_count,
            'Stav' => fn ($g) => new HtmlString($g->is_published ? '<span class="badge text-bg-success">publikovaný</span>' : '<span class="badge text-bg-secondary">koncept</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        return [
            ['name' => 'title', 'label' => 'Název', 'rules' => 'required|string|max:160', 'col' => 6],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:160', 'unique' => true, 'col' => 6],
            ['name' => 'product_id', 'label' => 'Produkt', 'type' => 'select', 'rules' => 'nullable|exists:products,id', 'options' => ['' => '–'] + self::opts(Product::orderBy('name')->get()), 'col' => 6],
            ['name' => 'car_model_id', 'label' => 'Auto', 'type' => 'select', 'rules' => 'nullable|exists:car_models,id', 'options' => ['' => '– obecný návod –'] + self::opts(CarModel::orderBy('brand')->get(), 'full_name'), 'col' => 6],
            ['name' => 'intro', 'label' => 'Úvod', 'type' => 'textarea', 'col' => 12],
            ['name' => 'video_minutes', 'label' => 'Délka videa (min)', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:255', 'col' => 3],
            ['name' => 'video_url', 'label' => 'Odkaz na video', 'rules' => 'nullable|url|max:255', 'col' => 6],
            ['name' => 'is_published', 'label' => 'Publikovat', 'type' => 'checkbox', 'default' => true, 'col' => 3],
            ['name' => 'steps', 'label' => 'Kroky', 'type' => 'steps', 'col' => 12],
        ];
    }
}
