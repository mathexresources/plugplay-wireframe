<?php

namespace App\Models;

class Page extends Base
{
    protected $casts = ['is_published' => 'bool'];
}
