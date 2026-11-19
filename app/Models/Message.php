<?php

namespace App\Models;

class Message extends Base
{
    public const TYPES = ['dotaz' => 'Dotaz', 'podpora' => 'Podpora s montáží', 'navrh' => 'Návrh produktu'];
    protected $casts = ['is_read' => 'bool'];
}
