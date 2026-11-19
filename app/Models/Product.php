<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Product extends Base
{
    public const GOALS = [
        'nabijeni' => ['Nabíjení', 'bi-lightning-charge'],
        'hudba' => ['Hudba a telefon', 'bi-music-note-beamed'],
        'bezpecnost' => ['Bezpečnost', 'bi-shield-check'],
        'pohodli' => ['Pohodlí', 'bi-cup-hot'],
    ];

    public const DIFFICULTY = [1 => 'snadná', 2 => 'střední', 3 => 'pokročilá'];
    public const DIFFICULTY_LONG = [
        1 => 'Jen zapojím do zásuvky',
        2 => 'Sundám kryt a zacvaknu konektor',
        3 => 'Zvládnu i kabeláž a kódování',
    ];

    protected $casts = ['is_active' => 'bool', 'is_universal' => 'bool', 'own_production' => 'bool', 'needs_service' => 'bool'];

    public function scopeActive($q) { return $q->where('is_active', true); }

    public function category() { return $this->belongsTo(Category::class); }
    public function zone() { return $this->belongsTo(Zone::class); }
    public function carModels() { return $this->belongsToMany(CarModel::class); }
    public function tools() { return $this->belongsToMany(Tool::class); }
    public function guides() { return $this->hasMany(Guide::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function approvedReviews() { return $this->hasMany(Review::class)->where('is_approved', true)->latest(); }

    public function fits(?CarModel $model): bool
    {
        if (! $model) return false;
        return $this->is_universal || $this->carModels->contains('id', $model->id);
    }

    public function guideFor(?CarModel $model): ?Guide
    {
        $guides = $this->guides->where('is_published', true);
        return ($model ? $guides->firstWhere('car_model_id', $model->id) : null)
            ?? $guides->firstWhere('car_model_id', null)
            ?? $guides->first();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function getInStockAttribute(): bool { return $this->stock > 0; }

    public function getAvailabilityAttribute(): string
    {
        return $this->stock > 0 ? 'Skladem – odešleme zítra' : "Na objednávku – do {$this->restock_days} dnů";
    }

    public function getDiscountPercentAttribute(): ?int
    {
        return $this->old_price && $this->old_price > $this->price
            ? (int) round(100 - $this->price / $this->old_price * 100) : null;
    }

    public function getInstallTimeAttribute(): string { return self::minutes($this->install_minutes); }

    public static function minutes(int $m): string
    {
        if ($m < 60) return "$m min";
        $h = intdiv($m, 60); $r = $m % 60;
        return $r ? "{$h} h {$r} min" : "{$h} h";
    }

    public function lines(string $field): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->$field))));
    }

    public function specList(): array
    {
        $out = [];
        foreach ($this->lines('specs') as $line) {
            [$k, $v] = array_pad(explode(':', $line, 2), 2, '');
            $out[trim($k)] = trim($v);
        }
        return $out;
    }

    public function variantList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->variant_options))));
    }

    public function getRatingAttribute(): ?float
    {
        $avg = $this->approvedReviews->avg('rating');
        return $avg ? round($avg, 1) : null;
    }
}
