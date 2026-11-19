<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'restaurant_id', 'address_id',
        'customer_name', 'customer_phone', 'delivery_instructions',
        'status', 'payment_method', 'payment_status',
        'food_subtotal', 'discount_amount', 'tax_amount', 'food_gst_amount', 'delivery_charge',
        'packaging_charge', 'grand_total', 'commission_percentage_applied',
        'commissionable_amount', 'commission_amount', 'restaurant_earning_amount',
        'delivery_partner_commission_percentage_applied', 'delivery_partner_commission_amount', 'delivery_partner_payout_amount',
        'coupon_id', 'cancellation_reason', 'accepted_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'food_subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'food_gst_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'delivery_partner_commission_percentage_applied' => 'decimal:2',
            'delivery_partner_commission_amount' => 'decimal:2',
            'delivery_partner_payout_amount' => 'decimal:2',
            'packaging_charge' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'commission_percentage_applied' => 'decimal:2',
            'commissionable_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'restaurant_earning_amount' => 'decimal:2',
            'accepted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function deliveryAssignments(): HasMany
    {
        return $this->hasMany(DeliveryAssignment::class)->latest('id');
    }

    public function deliveryAssignment(): HasOne
    {
        return $this->hasOne(DeliveryAssignment::class)->ofMany(['id' => 'max']);
    }

    public function earning(): HasOne
    {
        return $this->hasOne(RestaurantEarning::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
