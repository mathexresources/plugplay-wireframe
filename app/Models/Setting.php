<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Setting extends Base
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const DEFAULTS = [
        'shop_name' => 'PlugPlay.cz',
        'shop_email' => 'info@plugplay.cz',
        'shop_phone' => '+420 777 000 000',
        'shop_hours' => 'Po–Pá 9:00–17:00',
        'free_shipping_from' => '2500',
        'bank_account' => '123456789/0100',
        'iban' => 'CZ6501000000000123456789',
        'company' => 'PlugPlay (školní projekt)',
    ];

    public static function get(string $key): ?string
    {
        $all = Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());
        return $all[$key] ?? self::DEFAULTS[$key] ?? null;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }
}
