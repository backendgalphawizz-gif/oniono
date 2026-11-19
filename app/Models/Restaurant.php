<?php

namespace App\Models;

use App\Enums\RestaurantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'owner_name', 'email', 'phone', 'phone_verified_at', 'logo', 'description',
        'address', 'city', 'state', 'pincode', 'latitude', 'longitude',
        'bank_account_name', 'bank_account_number', 'bank_ifsc', 'bank_name',
        'gst_number', 'gst_enabled', 'default_gst_percentage',
        'status', 'rejection_reason', 'is_blocked', 'is_open', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'status' => RestaurantStatus::class,
            'is_blocked' => 'boolean',
            'is_open' => 'boolean',
            'is_featured' => 'boolean',
            'gst_enabled' => 'boolean',
            'default_gst_percentage' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(RestaurantUser::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RestaurantImage::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RestaurantDocument::class);
    }

    public function timings(): HasMany
    {
        return $this->hasMany(RestaurantTiming::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(RestaurantHoliday::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(RestaurantCommission::class);
    }

    public function activeCommission(): HasOne
    {
        return $this->hasOne(RestaurantCommission::class)->ofMany(
            ['id' => 'max'],
            fn ($query) => $query->where('is_active', true)
        );
    }

    public function cuisines(): BelongsToMany
    {
        return $this->belongsToMany(Cuisine::class, 'restaurant_cuisine');
    }

    public function foodItems(): HasMany
    {
        return $this->hasMany(FoodItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(RestaurantEarning::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isOperational(): bool
    {
        return $this->status === RestaurantStatus::Approved
            && ! $this->is_blocked
            && $this->is_open;
    }

    public function accountStatusLabel(): string
    {
        if ($this->is_blocked) {
            return 'Blocked';
        }

        return match ($this->status) {
            RestaurantStatus::Suspended => 'Suspended',
            RestaurantStatus::Rejected => 'Rejected',
            RestaurantStatus::Pending => 'Pending approval',
            RestaurantStatus::Approved => 'Active',
        };
    }
}
