<?php

namespace App\Models;

class Bundle extends Base
{
    protected $casts = ['is_active' => 'bool'];
    public function carModel() { return $this->belongsTo(CarModel::class); }
    public function products() { return $this->belongsToMany(Product::class); }
    public function getFullPriceAttribute(): int { return (int) $this->products->sum('price'); }
    public function getInstallMinutesAttribute(): int { return (int) $this->products->sum('install_minutes'); }
}
