<?php

namespace App\Models;

class OrderItem extends Base
{
    public function order() { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
