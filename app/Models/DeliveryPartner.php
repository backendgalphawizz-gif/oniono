<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DeliveryPartner extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'avatar', 'vehicle_type',
        'vehicle_number', 'status', 'rejection_reason', 'is_available', 'is_active',
        'is_blocked', 'is_suspended',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DeliveryDocument::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(DeliveryPartnerEarning::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(DeliveryPartnerCommission::class);
    }

    public function activeCommission(): HasOne
    {
        return $this->hasOne(DeliveryPartnerCommission::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('is_active', true)
        );
    }

    public function accountStatusLabel(): string
    {
        if ($this->is_blocked) {
            return 'Blocked';
        }

        if ($this->is_suspended) {
            return 'Suspended';
        }

        if ($this->status === 'rejected') {
            return 'Rejected';
        }

        if ($this->status === 'pending') {
            return 'Pending approval';
        }

        return 'Active';
    }
}
