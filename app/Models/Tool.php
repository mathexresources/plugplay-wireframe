<?php

namespace App\Models;

class Tool extends Base
{
    public function product() { return $this->belongsTo(Product::class); }
    public function products() { return $this->belongsToMany(Product::class); }
}
