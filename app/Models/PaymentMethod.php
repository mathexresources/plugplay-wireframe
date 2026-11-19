<?php

namespace App\Models;

class PaymentMethod extends Base
{
    protected $casts = ['is_active' => 'bool'];
}
