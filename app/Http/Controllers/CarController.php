<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Product;
use App\Models\Zone;
use App\Support\CarContext;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function select(Request $request)
    {
        if ($request->filled('car_id') && auth()->check()) {
            $car = Car::where('user_id', auth()->id())->findOrFail($request->integer('car_id'));
            CarContext::activate($car);
            return redirect()->route('car.show', $car->carModel->slug);
        }
        $data = $request->validate(['car_model_id' => 'required|exists:car_models,id', 'body_type' => 'nullable|string|max:40']);
        $model = CarModel::findOrFail($data['car_model_id']);
        CarContext::set($model, $data['body_type'] ?? null);
        return redirect()->route('car.show', $model->slug);
    }

    public function clear()
    {
        CarContext::set(null);
        return redirect()->route('home');
    }

    public function show(Request $request, string $slug)
    {
        $model = CarModel::where('slug', $slug)->firstOrFail();
        if (CarContext::model()?->id !== $model->id) {
            CarContext::set($model, CarContext::body());
        }

        $zones = Zone::orderBy('sort')->get();
        $all = $model->fittingProductsQuery()->with(['zone', 'approvedReviews'])->get();
        foreach ($zones as $z) $z->count = $all->where('zone_id', $z->id)->count();

        $zone = $zones->firstWhere('slug', $request->query('zona')) ?? $zones->sortByDesc('count')->first();
        $goal = array_key_exists($request->query('cil'), Product::GOALS) ? $request->query('cil') : null;
        $maxDiff = in_array((int) $request->query('narocnost'), [1, 2, 3]) ? (int) $request->query('narocnost') : 3;
        $sort = $request->query('razeni', 'oblibene');

        $products = $all->where('zone_id', $zone?->id)
            ->when($goal, fn ($c) => $c->where('goal', $goal));
        $products = match ($sort) {
            'snadne' => $products->sortBy('difficulty'),
            'levne' => $products->sortBy('price'),
            default => $products->sortByDesc('sold_count'),
        };
        // harder than chosen → greyed out and at the end of the list
        $products = $products->sortBy(fn ($p) => $p->difficulty > $maxDiff ? 1 : 0, SORT_REGULAR)->values();

        $bundles = $model->bundles()->where('is_active', true)->with('products')->get();
        $garageCar = CarContext::car();

        return view('shop.car', compact('model', 'zones', 'zone', 'products', 'goal', 'maxDiff', 'sort', 'bundles', 'all', 'garageCar'));
    }
}
