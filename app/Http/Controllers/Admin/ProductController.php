<?php

namespace App\Http\Controllers\Admin;

use App\Models\CarModel;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tool;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class ProductController extends CrudController
{
    protected string $model = Product::class;
    protected string $route = 'admin.products';
    protected string $title = 'Produkty';
    protected string $singular = 'produkt';
    protected array $search = ['name', 'sku', 'manufacturer'];
    protected array $with = ['category'];
    protected ?string $slugFrom = 'name';

    protected function filter($q, Request $request): void
    {
        if ($request->filled('kategorie')) $q->where('category_id', $request->integer('kategorie'));
        if ($request->query('sklad') === 'dochazi') $q->where('stock', '<=', 5);
        if ($request->query('stav') === 'skryte') $q->where('is_active', false);
    }

    protected function filters(): array
    {
        return [
            ['kategorie', 'Kategorie', ['' => 'Všechny kategorie'] + self::opts(Category::orderBy('name')->get())],
            ['sklad', 'Sklad', ['' => 'Celý sklad', 'dochazi' => 'Dochází (≤ 5 ks)']],
            ['stav', 'Stav', ['' => 'Všechny', 'skryte' => 'Skryté']],
        ];
    }

    protected function columns(): array
    {
        return [
            'Produkt' => fn ($p) => new HtmlString('<b>'.e($p->name).'</b><div class="small text-secondary">'.e($p->sku).' · '.e($p->category?->name).'</div>'),
            'Cena' => fn ($p) => czk($p->price),
            'Sklad' => fn ($p) => new HtmlString('<span class="'.($p->stock <= 5 ? 'text-danger fw-bold' : '').'">'.$p->stock.' ks</span>'),
            'Prodáno' => fn ($p) => $p->sold_count,
            'Stav' => fn ($p) => new HtmlString($p->is_active ? '<span class="badge text-bg-success">aktivní</span>' : '<span class="badge text-bg-secondary">skrytý</span>'),
        ];
    }

    protected function fields(?Model $item): array
    {
        $cats = Category::with('parent')->orderBy('sort')->get();
        return [
            ['name' => 'name', 'label' => 'Název', 'rules' => 'required|string|max:160', 'col' => 8],
            ['name' => 'sku', 'label' => 'Kód (SKU)', 'rules' => 'required|string|max:40', 'unique' => true, 'col' => 4],
            ['name' => 'slug', 'label' => 'URL (slug)', 'rules' => 'required|alpha_dash|max:160', 'unique' => true, 'col' => 6, 'help' => 'Nech prázdné a vytvoří se z názvu.'],
            ['name' => 'category_id', 'label' => 'Kategorie', 'type' => 'select', 'rules' => 'nullable|exists:categories,id', 'options' => ['' => '–'] + self::opts($cats, fn ($c) => ($c->parent ? $c->parent->name.' › ' : '').$c->name), 'col' => 3],
            ['name' => 'zone_id', 'label' => 'Část auta (zóna)', 'type' => 'select', 'rules' => 'nullable|exists:zones,id', 'options' => ['' => '–'] + self::opts(Zone::orderBy('sort')->get()), 'col' => 3],
            ['name' => 'short_description', 'label' => 'Krátký popis', 'rules' => 'nullable|string|max:255', 'col' => 12],
            ['name' => 'description', 'label' => 'Popis', 'type' => 'textarea', 'col' => 12],
            ['name' => 'price', 'label' => 'Cena s DPH (Kč)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'old_price', 'label' => 'Původní cena (přeškrtnutá)', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'col' => 3],
            ['name' => 'stock', 'label' => 'Skladem (ks)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'restock_days', 'label' => 'Dodání bez skladu (dní)', 'type' => 'number', 'rules' => 'required|integer|min:1|max:90', 'col' => 3],
            ['name' => 'difficulty', 'label' => 'Náročnost montáže', 'type' => 'select', 'rules' => 'required|in:1,2,3', 'options' => Product::DIFFICULTY, 'col' => 3],
            ['name' => 'install_minutes', 'label' => 'Čas montáže (min)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'col' => 3],
            ['name' => 'install_priority', 'label' => 'Pořadí v plánu', 'type' => 'number', 'rules' => 'required|integer|min:0|max:999', 'col' => 3, 'help' => 'Nižší = montuje se dřív'],
            ['name' => 'goal', 'label' => 'Cíl zákazníka', 'type' => 'select', 'rules' => 'nullable|in:'.implode(',', array_keys(Product::GOALS)), 'options' => ['' => '–'] + collect(Product::GOALS)->map(fn ($g) => $g[0])->all(), 'col' => 3],
            ['name' => 'manufacturer', 'label' => 'Výrobce', 'rules' => 'nullable|string|max:80', 'col' => 4],
            ['name' => 'location_in_car', 'label' => 'Umístění v autě', 'rules' => 'nullable|string|max:120', 'col' => 4],
            ['name' => 'video_url', 'label' => 'Odkaz na video', 'rules' => 'nullable|url|max:255', 'col' => 4],
            ['name' => 'install_tip', 'label' => 'Tip k montáži (v plánu)', 'rules' => 'nullable|string|max:255', 'col' => 12],
            ['name' => 'variant_label', 'label' => 'Název varianty', 'rules' => 'nullable|string|max:60', 'col' => 4, 'help' => 'např. Barva rámečku'],
            ['name' => 'variant_options', 'label' => 'Varianty (oddělené čárkou)', 'rules' => 'nullable|string|max:255', 'col' => 8],
            ['name' => 'test_score', 'label' => 'Hodnocení testu (0–10)', 'type' => 'number', 'rules' => 'nullable|integer|between:0,10', 'col' => 3],
            ['name' => 'test_quote', 'label' => 'Citát z testu', 'rules' => 'nullable|string|max:255', 'col' => 9],
            ['name' => 'pros', 'label' => 'Výhody (každá na řádek)', 'type' => 'textarea', 'col' => 6],
            ['name' => 'cons', 'label' => 'Nevýhody (každá na řádek)', 'type' => 'textarea', 'col' => 6],
            ['name' => 'specs', 'label' => 'Parametry („Název: hodnota“ na řádek)', 'type' => 'textarea', 'col' => 12],
            ['name' => 'car_models', 'relation' => 'carModels', 'label' => 'Ověřeno v autech', 'type' => 'multiselect', 'options' => self::opts(CarModel::orderBy('brand')->get(), 'full_name'), 'value' => $item?->carModels->pluck('id')->all() ?? [], 'col' => 6],
            ['name' => 'tools', 'label' => 'Potřebné nářadí', 'type' => 'multiselect', 'options' => self::opts(Tool::orderBy('name')->get()), 'value' => $item?->tools->pluck('id')->all() ?? [], 'col' => 6],
            ['name' => 'image', 'label' => 'Hlavní fotka', 'type' => 'image', 'dir' => 'products', 'col' => 6],
            ['name' => 'is_active', 'label' => 'Zobrazit v e-shopu', 'type' => 'checkbox', 'default' => true, 'col' => 3],
            ['name' => 'is_universal', 'label' => 'Univerzální (pasuje do všech aut)', 'type' => 'checkbox', 'col' => 3],
            ['name' => 'own_production', 'label' => 'Vlastní výroba', 'type' => 'checkbox', 'col' => 3],
            ['name' => 'needs_service', 'label' => 'Doporučit servis', 'type' => 'checkbox', 'col' => 3],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        return null; // order items keep a name snapshot, product_id becomes null
    }
}
