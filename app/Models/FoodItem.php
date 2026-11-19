<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'slug', 'description', 'image',
        'price', 'is_gst_applicable', 'gst_percentage', 'food_type', 'preparation_time', 'is_available', 'is_sold_out', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_gst_applicable' => 'boolean',
            'gst_percentage' => 'decimal:2',
            'is_available' => 'boolean',
            'is_sold_out' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FoodVariant::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(FoodAddon::class);
    }

    public function scopeForRestaurant(Builder $query, int $restaurantId): Builder
    {
        return $query->where('restaurant_id', $restaurantId);
    }
}
