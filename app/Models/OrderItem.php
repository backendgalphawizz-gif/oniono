<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'food_item_id', 'item_name', 'quantity',
        'unit_price', 'total_price', 'variant_snapshot', 'addons_snapshot',
        'is_gst_applicable', 'gst_percentage', 'gst_amount',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'is_gst_applicable' => 'boolean',
            'gst_percentage' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'variant_snapshot' => 'array',
            'addons_snapshot' => 'array',
        ];
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
