<?php

namespace App\Support;

use App\Models\Bundle;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Session cart ("plán úprav"). Lines are keyed so the same product in two variants stays separate.
 * line = ['type' => 'product'|'bundle', 'id' => int, 'variant' => ?string, 'qty' => int]
 */
class Cart
{
    public static function raw(): array { return (array) (session('cart') ?? []); }

    public static function save(array $lines): void { session(['cart' => $lines]); }

    public static function add(string $type, int $id, int $qty = 1, ?string $variant = null): void
    {
        $lines = self::raw();
        $key = $type.'-'.$id.($variant ? '-'.md5($variant) : '');
        $lines[$key] = ['type' => $type, 'id' => $id, 'variant' => $variant,
            'qty' => min(99, ($lines[$key]['qty'] ?? 0) + max(1, $qty))];
        self::save($lines);
    }

    public static function update(string $key, int $qty): void
    {
        $lines = self::raw();
        if (! isset($lines[$key])) return;
        if ($qty <= 0) unset($lines[$key]); else $lines[$key]['qty'] = min(99, $qty);
        self::save($lines);
    }

    public static function clear(): void { session()->forget(['cart', 'coupon']); }

    public static function count(): int { return (int) collect(self::raw())->sum('qty'); }

    /** Resolved lines sorted by recommended install order. */
    public static function items(): Collection
    {
        $raw = collect(self::raw());
        $products = Product::with(['tools.product', 'zone'])->whereIn('id', $raw->where('type', 'product')->pluck('id'))->get()->keyBy('id');
        $bundles = Bundle::with('products.tools.product')->whereIn('id', $raw->where('type', 'bundle')->pluck('id'))->get()->keyBy('id');

        $items = collect();
        foreach ($raw as $key => $line) {
            $model = $line['type'] === 'bundle' ? $bundles->get($line['id']) : $products->get($line['id']);
            if (! $model || ($line['type'] === 'product' && ! $model->is_active)) continue;
            $minutes = $line['type'] === 'bundle' ? $model->install_minutes : $model->install_minutes;
            $items->push((object) [
                'key' => $key, 'type' => $line['type'], 'model' => $model, 'variant' => $line['variant'],
                'qty' => $line['qty'], 'price' => $model->price, 'total' => $model->price * $line['qty'],
                'minutes' => $minutes,
                'priority' => $line['type'] === 'bundle' ? 0 : $model->install_priority,
                'tools' => $line['type'] === 'bundle' ? $model->products->flatMap->tools : $model->tools,
            ]);
        }
        return $items->sortBy('priority')->values();
    }

    public static function summary(?Collection $items = null): object
    {
        $items ??= self::items();
        $subtotal = (int) $items->sum('total');
        $coupon = null; $discount = 0; $couponError = null;
        if ($code = session('coupon')) {
            $coupon = Coupon::where('code', $code)->first();
            $couponError = $coupon ? $coupon->problemFor($subtotal) : 'Kód neexistuje.';
            if ($couponError) $coupon = null; else $discount = $coupon->discountFor($subtotal);
        }
        $freeFrom = (int) Setting::get('free_shipping_from');
        $afterDiscount = $subtotal - $discount;

        return (object) [
            'subtotal' => $subtotal, 'discount' => $discount, 'coupon' => $coupon, 'couponError' => $couponError,
            'total' => $afterDiscount, 'minutes' => (int) $items->sum(fn ($i) => $i->minutes),
            'count' => (int) $items->sum('qty'), 'freeFrom' => $freeFrom,
            'freeShipping' => $freeFrom > 0 && $afterDiscount >= $freeFrom,
            'missingForFree' => max(0, $freeFrom - $afterDiscount),
            'tools' => $items->flatMap(fn ($i) => $i->tools)->unique('id')->values(),
        ];
    }
}
