<?php

namespace App\Models;

class CarUpgrade extends Base
{
    protected $casts = ['installed' => 'bool'];
    public function car() { return $this->belongsTo(Car::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
