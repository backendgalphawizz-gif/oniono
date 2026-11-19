<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Settlement extends Model
{
    protected $fillable = [
        'settlement_number', 'restaurant_id', 'processed_by_admin_id',
        'amount', 'payment_mode', 'transaction_reference', 'payment_date',
        'admin_remarks', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SettlementStatus::class,
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by_admin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SettlementItem::class);
    }
}
