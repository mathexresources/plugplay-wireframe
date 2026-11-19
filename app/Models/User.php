<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = ['id', 'is_admin'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_admin' => 'bool',
            'notify_upgrades' => 'bool', 'notify_deals' => 'bool'];
    }

    public function cars() { return $this->hasMany(Car::class)->orderByDesc('is_active')->orderBy('id'); }
    public function activeCar() { return $this->hasOne(Car::class)->where('is_active', true); }
    public function orders() { return $this->hasMany(Order::class)->latest(); }
    public function addresses() { return $this->hasMany(Address::class)->orderByDesc('is_default'); }
    public function plans() { return $this->hasMany(SavedPlan::class)->latest(); }

    public function getFirstNameAttribute(): string { return explode(' ', trim($this->name))[0]; }
}
