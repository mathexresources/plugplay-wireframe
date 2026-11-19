<?php

namespace App\Models;

class ShippingMethod extends Base
{
    protected $casts = ['needs_address' => 'bool', 'needs_pickup_point' => 'bool', 'free_over_threshold' => 'bool', 'is_active' => 'bool'];
}
