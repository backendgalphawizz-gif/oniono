<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'image', 'sort_order', 'is_active', 'parent_id', 'restaurant_id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function foodItems(): HasMany
    {
        return $this->hasMany(FoodItem::class);
    }

    public function scopeOwnedByRestaurant(Builder $query, int $restaurantId): Builder
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeMain(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSub(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function isMain(): bool
    {
        return $this->parent_id === null;
    }

    public function isSub(): bool
    {
        return $this->parent_id !== null;
    }

    public function displayLabel(): string
    {
        if ($this->isSub() && $this->relationLoaded('parent') && $this->parent) {
            return $this->parent->name.' › '.$this->name;
        }

        if ($this->isSub() && $this->parent) {
            return $this->parent->name.' › '.$this->name;
        }

        return $this->name;
    }

    /**
     * Sub-categories available for menu / food item assignment.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    /** Platform sub-categories (admin catalog). */
    public static function menuOptions()
    {
        return static::query()
            ->sub()
            ->with('parent')
            ->whereNull('restaurant_id')
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (self $category) => strtolower(($category->parent?->name ?? '').' '.$category->name))
            ->values();
    }

    /** Platform + this restaurant's custom sub-categories. */
    public static function menuOptionsForRestaurant(int $restaurantId)
    {
        return static::query()
            ->sub()
            ->with('parent')
            ->where('is_active', true)
            ->where(function (Builder $query) use ($restaurantId) {
                $query->whereNull('restaurant_id')
                    ->orWhere('restaurant_id', $restaurantId);
            })
            ->get()
            ->sortBy(fn (self $category) => strtolower(($category->parent?->name ?? '').' '.$category->name))
            ->values();
    }

    public static function generateUniqueSlug(string $name, ?int $parentId = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);

        if ($parentId) {
            $parent = static::query()->find($parentId);
            $base = Str::slug(($parent?->slug ?? 'category').'-'.$name);
        }

        $slug = $base;
        $suffix = 1;

        while (static::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
