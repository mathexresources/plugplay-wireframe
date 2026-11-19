<?php

namespace App\Models;

class Zone extends Base
{
    public function products() { return $this->hasMany(Product::class); }
}
