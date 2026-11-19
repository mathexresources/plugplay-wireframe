<?php

namespace App\Models;

class Coupon extends Base
{
    protected $casts = ['valid_until' => 'date', 'is_active' => 'bool'];

    /** Returns error message or null when the coupon can be used for given subtotal. */
    public function problemFor(int $subtotal): ?string
    {
        if (! $this->is_active) return 'Tento kód už neplatí.';
        if ($this->valid_until && $this->valid_until->endOfDay()->isPast()) return 'Platnost kódu vypršela.';
        if ($this->usage_limit !== null && $this->used >= $this->usage_limit) return 'Kód už byl vyčerpán.';
        if ($subtotal < $this->min_total) return 'Kód platí pro nákup od '.number_format($this->min_total, 0, ',', ' ').' Kč.';
        return null;
    }

    public function discountFor(int $subtotal): int
    {
        $d = $this->type === 'percent' ? (int) round($subtotal * $this->value / 100) : $this->value;
        return min($d, $subtotal);
    }

    public function getLabelAttribute(): string
    {
        return $this->type === 'percent' ? "−{$this->value} %" : '−'.number_format($this->value, 0, ',', ' ').' Kč';
    }
}
