<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Car extends Base
{
    protected $casts = ['is_active' => 'bool'];
    public function user() { return $this->belongsTo(User::class); }
    public function carModel() { return $this->belongsTo(CarModel::class); }
    public function upgrades() { return $this->hasMany(CarUpgrade::class); }

    public function getLabelAttribute(): string { return $this->nickname ?: $this->carModel->short_name; }
    public function getPhotoUrlAttribute(): ?string { return $this->photo ? Storage::url($this->photo) : null; }
}
