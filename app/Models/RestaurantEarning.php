<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RestaurantEarning extends Model
{
    protected $fillable = [
        'restaurant_id', 'order_id', 'food_subtotal', 'discount_amount',
        'commissionable_amount', 'commission_percentage', 'commission_amount',
        'net_earning', 'status', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'food_subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'commissionable_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'net_earning' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function settlementItem(): HasOne
    {
        return $this->hasOne(SettlementItem::class);
    }
}
