<?php

namespace App\Models;

class GuideProgress extends Base
{
    protected $table = 'guide_progress';
    protected $casts = ['done' => 'bool'];
    public function guide() { return $this->belongsTo(Guide::class); }
}
