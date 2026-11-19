<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Page;
use App\Models\CarModel;
use App\Support\Cart;
use App\Support\CarContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $view->with([
                'navCategories' => Category::whereNull('parent_id')->orderBy('sort')->get(),
                'navCar' => CarContext::model(),
                'navCars' => $user ? $user->cars()->with('carModel')->get() : collect(),
                'cartCount' => Cart::count(),
                'footerPages' => Page::where('is_published', true)->orderBy('sort')->get()->groupBy('group'),
                'footerModels' => CarModel::where('is_popular', true)->orderBy('sort')->take(4)->get(),
            ]);
        });
    }
}
