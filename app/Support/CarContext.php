<?php

namespace App\Support;

use App\Models\Car;
use App\Models\CarModel;

/** The car the whole site is currently tailored to (guest: session, user: active garage car). */
class CarContext
{
    private static ?CarModel $model = null;
    private static bool $resolved = false;

    public static function model(): ?CarModel
    {
        if (! self::$resolved) {
            self::$resolved = true;
            $id = session('car_model_id');
            if (! $id && auth()->check()) {
                $id = auth()->user()->activeCar?->car_model_id;
            }
            self::$model = $id ? CarModel::find($id) : null;
        }
        return self::$model;
    }

    public static function body(): ?string { return session('car_body'); }

    public static function set(?CarModel $model, ?string $body = null): void
    {
        session(['car_model_id' => $model?->id, 'car_body' => $body]);
        self::$model = $model;
        self::$resolved = true;
    }

    /** Garage car matching the current model, for logged in users. */
    public static function car(): ?Car
    {
        if (! auth()->check() || ! self::model()) return null;
        return auth()->user()->cars()->where('car_model_id', self::model()->id)->orderByDesc('is_active')->first();
    }

    public static function activate(Car $car): void
    {
        Car::where('user_id', $car->user_id)->update(['is_active' => false]);
        $car->update(['is_active' => true]);
        self::set($car->carModel, $car->body_type);
    }
}
