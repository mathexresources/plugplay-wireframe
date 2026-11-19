<?php

namespace App\Models;

class Review extends Base
{
    protected $casts = ['is_approved' => 'bool'];
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function carModel() { return $this->belongsTo(CarModel::class); }
}
