<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use App\Models\Guide;
use App\Models\GuideProgress;
use App\Support\CarContext;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $car = $request->filled('auto') ? CarModel::find($request->integer('auto')) : CarContext::model();
        $guides = Guide::with(['product', 'carModel'])->where('is_published', true)
            ->when($car, fn ($q) => $q->where(fn ($w) => $w->where('car_model_id', $car->id)->orWhereNull('car_model_id')))
            ->when($request->boolean('video'), fn ($q) => $q->whereNotNull('video_minutes'))
            ->orderByRaw('car_model_id is null')->orderBy('title')->get();
        $models = CarModel::orderBy('brand')->orderBy('name')->get();
        return view('shop.guides', compact('guides', 'car', 'models'));
    }

    public function show(string $slug)
    {
        $guide = Guide::with(['product.tools.product', 'carModel'])->where('slug', $slug)->where('is_published', true)->firstOrFail();
        $progress = auth()->check() ? GuideProgress::where('user_id', auth()->id())->where('guide_id', $guide->id)->first() : null;
        return view('shop.guide', compact('guide', 'progress'));
    }

    public function progress(Request $request, Guide $guide)
    {
        $step = max(1, min($guide->step_count, $request->integer('step', 1)));
        GuideProgress::updateOrCreate(['user_id' => auth()->id(), 'guide_id' => $guide->id],
            ['step' => $step, 'done' => $request->boolean('done')]);
        return back()->with('ok', $request->boolean('done') ? 'Hotovo! Montáž je dokončená.' : "Uloženo – pokračuješ krokem $step.");
    }
}
