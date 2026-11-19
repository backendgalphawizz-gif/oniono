<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'status', 'amount', 'gateway', 'gateway_order_id',
        'gateway_payment_id', 'gateway_signature', 'meta',
        'cod_collection_status', 'cod_collected_amount', 'cod_collected_at',
        'cod_collected_by_type', 'cod_collected_by_id',
        'cod_remittance_status', 'cod_reconciliation_status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'cod_collected_amount' => 'decimal:2',
            'meta' => 'array',
            'cod_collected_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
