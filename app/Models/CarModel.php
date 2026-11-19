<?php

namespace App\Models;

class CarModel extends Base
{
    public function products() { return $this->belongsToMany(Product::class); }
    public function bundles() { return $this->hasMany(Bundle::class); }
    public function guides() { return $this->hasMany(Guide::class); }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->name}".($this->code ? " ({$this->code})" : ''));
    }

    public function getShortNameAttribute(): string
    {
        return trim($this->brand.' '.($this->code ?: $this->name));
    }

    public function getYearsAttribute(): string
    {
        if (! $this->year_from) return '';
        return $this->year_from.'–'.($this->year_to ?: 'dosud');
    }

    public function bodyTypeList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->body_types))));
    }

    /** Products that fit this car: explicitly compatible + universal ones. */
    public function fittingProductsQuery()
    {
        return Product::active()->where(fn ($q) => $q->where('is_universal', true)
            ->orWhereHas('carModels', fn ($c) => $c->where('car_models.id', $this->id)));
    }
}
