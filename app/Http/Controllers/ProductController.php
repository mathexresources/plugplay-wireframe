<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\Product;
use App\Support\CarContext;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $product = Product::active()->where('slug', $slug)
            ->with(['category.parent', 'carModels', 'tools.product', 'guides', 'approvedReviews', 'zone'])->firstOrFail();

        $car = CarContext::model();
        $fits = $product->fits($car);
        // "Pasuje do tvého auta?" checker in classic mode
        $checked = $request->filled('over') ? CarModel::find($request->integer('over')) : null;
        $guide = $product->guideFor($car);

        $reviews = $product->approvedReviews;
        $onlyMine = $car && $request->query('recenze') !== 'vse';
        $myReviews = $car ? $reviews->where('car_model_id', $car->id) : collect();
        $shownReviews = $onlyMine && $myReviews->isNotEmpty() ? $myReviews : $reviews;

        $crossSell = Product::active()->where('id', '!=', $product->id)
            ->when($car, fn ($q) => $q->where(fn ($w) => $w->where('is_universal', true)->orWhereHas('carModels', fn ($c) => $c->where('car_models.id', $car->id))))
            ->where(fn ($w) => $w->where('category_id', '!=', $product->category_id)->orWhereNull('category_id'))
            ->orderByDesc('sold_count')->take(3)->get();

        $carModels = CarModel::orderBy('brand')->orderBy('name')->get();
        $tour = $car && $fits; // "průvodce" mode = we know the car and the product fits

        return view('shop.product', compact('product', 'car', 'fits', 'checked', 'guide', 'reviews', 'myReviews', 'shownReviews', 'onlyMine', 'crossSell', 'carModels', 'tour'));
    }

    public function review(Request $request, Product $product)
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:60',
            'rating' => 'required|integer|between:1,5',
            'install_minutes' => 'nullable|integer|min:0|max:1000',
            'body' => 'required|string|min:10|max:2000',
        ]);
        $car = CarContext::model();
        $product->reviews()->create($data + [
            'user_id' => auth()->id(), 'car_model_id' => $car?->id,
            'car_label' => $car?->full_name, 'is_approved' => false,
        ]);
        return back()->with('ok', 'Díky za recenzi! Po schválení se zobrazí u produktu.');
    }
}
