<?php

namespace App\Models;

class Guide extends Base
{
    protected $casts = ['steps' => 'array', 'is_published' => 'bool'];
    public function product() { return $this->belongsTo(Product::class); }
    public function carModel() { return $this->belongsTo(CarModel::class); }
    public function progress() { return $this->hasMany(GuideProgress::class); }
    public function getStepCountAttribute(): int { return count($this->steps ?? []); }
}
