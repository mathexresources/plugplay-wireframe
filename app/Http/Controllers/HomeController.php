<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\Product;
use App\Models\Showcase;

class HomeController extends Controller
{
    public function index()
    {
        $models = CarModel::orderBy('sort')->get();
        $popular = $models->where('is_popular', true)->take(5)->map(function ($m) {
            $m->upgrade_count = $m->fittingProductsQuery()->where('is_universal', false)->count()
                + Product::active()->where('is_universal', true)->count();
            return $m;
        });
        $showcases = Showcase::with('product')->where('is_approved', true)->latest()->take(3)->get();
        // Fallback showcase cards built from top products when nobody has uploaded photos yet
        $featured = Product::active()->where('is_universal', false)->orderByDesc('sold_count')->take(3)->get();

        $carsJson = $models->groupBy('brand')->map(fn ($g) => $g->map(fn ($m) => [
            'id' => $m->id, 'label' => $m->name.($m->code ? " ({$m->code})" : ''), 'years' => $m->years, 'bodies' => $m->bodyTypeList(),
        ])->values());

        return view('shop.home', compact('popular', 'showcases', 'featured', 'carsJson'));
    }
}
