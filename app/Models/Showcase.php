<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Showcase extends Base
{
    protected $casts = ['is_approved' => 'bool'];
    public function product() { return $this->belongsTo(Product::class); }
    public function url(?string $path): ?string { return $path ? Storage::url($path) : null; }
}
