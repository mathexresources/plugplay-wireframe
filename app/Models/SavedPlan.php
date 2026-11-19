<?php

namespace App\Models;

class SavedPlan extends Base
{
    protected $casts = ['items' => 'array'];
    public function carModel() { return $this->belongsTo(CarModel::class); }
}
