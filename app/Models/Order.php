<?php

namespace App\Models;

class Order extends Base
{
    public const STATUSES = [
        'new' => ['Přijata', 'secondary'],
        'packing' => ['Balíme', 'warning'],
        'shipped' => ['Na cestě', 'info'],
        'delivered' => ['Doručeno', 'success'],
        'cancelled' => ['Zrušena', 'danger'],
    ];

    protected $casts = ['is_paid' => 'bool'];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function carModel() { return $this->belongsTo(CarModel::class); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status][1] ?? 'secondary'; }

    public function getRouteKeyName(): string { return 'number'; }
}
