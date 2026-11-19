<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\Category;
use App\Models\Guide;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request, ?string $category = null)
    {
        $cat = $category ? Category::where('slug', $category)->with('children', 'parent')->firstOrFail() : null;
        $topCats = Category::whereNull('parent_id')->with('children')->orderBy('sort')->get();

        $q = Product::active()->with(['approvedReviews', 'category']);
        if ($cat) $q->whereIn('category_id', $cat->descendantIds());

        $base = clone $q; // for manufacturer facets
        $model = $request->filled('auto') ? CarModel::find($request->integer('auto')) : null;
        if ($model) $q->where(fn ($w) => $w->where('is_universal', true)->orWhereHas('carModels', fn ($c) => $c->where('car_models.id', $model->id)));
        if ($request->filled('cena_od')) $q->where('price', '>=', $request->integer('cena_od'));
        if ($request->filled('cena_do')) $q->where('price', '<=', $request->integer('cena_do'));
        $diffs = array_filter(array_map('intval', (array) $request->input('narocnost', [])));
        if ($diffs) $q->whereIn('difficulty', $diffs);
        $makers = array_filter((array) $request->input('vyrobce', []));
        if ($makers) $q->whereIn('manufacturer', $makers);
        if ($request->boolean('skladem')) $q->where('stock', '>', 0);
        if ($request->boolean('video')) $q->whereHas('guides', fn ($g) => $g->whereNotNull('video_minutes'));
        if ($request->boolean('sleva')) $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');

        match ($request->query('razeni', 'prodej')) {
            'levne' => $q->orderBy('price'),
            'drahe' => $q->orderByDesc('price'),
            'nove' => $q->latest('id'),
            'hodnoceni' => $q->withAvg(['reviews' => fn ($r) => $r->where('is_approved', true)], 'rating')->orderByDesc('reviews_avg_rating'),
            default => $q->orderByDesc('sold_count'),
        };

        $products = $q->paginate(12)->withQueryString();
        $manufacturers = $base->whereNotNull('manufacturer')->selectRaw('manufacturer, count(*) as cnt')->groupBy('manufacturer')->orderByDesc('cnt')->pluck('cnt', 'manufacturer');
        $carModels = CarModel::orderBy('brand')->orderBy('name')->get();
        $view = $request->query('zobrazeni') === 'seznam' ? 'list' : 'grid';

        return view('shop.catalog', compact('cat', 'topCats', 'products', 'manufacturers', 'carModels', 'model', 'view'));
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q'));
        $products = collect(); $guides = collect();
        if (mb_strlen($term) >= 2) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
            $products = Product::active()->with('approvedReviews')->where(fn ($w) => $w->where('name', 'like', $like)
                ->orWhere('short_description', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('manufacturer', 'like', $like))
                ->orderByDesc('sold_count')->limit(24)->get();
            $guides = Guide::where('is_published', true)->where('title', 'like', $like)->limit(12)->get();
        }
        return view('shop.search', compact('term', 'products', 'guides'));
    }
}
