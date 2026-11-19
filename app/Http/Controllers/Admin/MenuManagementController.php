<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\FoodItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuManagementController extends Controller
{
    use AppliesAdminGeoScope;

    public function index()
    {
        $stats = [
            'categories' => Category::query()->main()->count(),
            'sub_categories' => Category::query()->sub()->count(),
            'cuisines' => Cuisine::query()->count(),
            'food_items' => $this->scopeFoodItems(FoodItem::query())->count(),
            'featured_restaurants' => $this->scopeRestaurants(Restaurant::query())->where('is_featured', true)->count(),
            'featured_food_items' => $this->scopeFoodItems(FoodItem::query())->where('is_featured', true)->count(),
        ];

        return view('admin.menu.index', compact('stats'));
    }

    public function featured(Request $request)
    {
        $featuredRestaurants = $this->scopeRestaurants(Restaurant::query())
            ->where('is_featured', true)
            ->orderBy('name')
            ->get();

        $featuredItems = $this->scopeFoodItems(FoodItem::query())
            ->with(['restaurant', 'category'])
            ->where('is_featured', true)
            ->latest()
            ->get();

        $restaurantSearch = $this->scopeRestaurants(Restaurant::query())
            ->when($request->filled('restaurant_search'), function ($q) use ($request) {
                $s = $request->string('restaurant_search')->trim();
                $q->where('name', 'like', "%{$s}%");
            })
            ->where('is_featured', false)
            ->orderBy('name')
            ->limit(15)
            ->get();

        $foodSearch = $this->scopeFoodItems(FoodItem::query())
            ->with('restaurant')
            ->when($request->filled('food_search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('food_search')->trim().'%'))
            ->where('is_featured', false)
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.menu.featured', compact(
            'featuredRestaurants',
            'featuredItems',
            'restaurantSearch',
            'foodSearch',
        ));
    }

    public function toggleRestaurantFeatured(Restaurant $restaurant)
    {
        $this->ensureRestaurantInScope($restaurant);
        $restaurant->update(['is_featured' => ! $restaurant->is_featured]);

        return back()->with('success', $restaurant->is_featured ? 'Restaurant marked as featured.' : 'Restaurant removed from featured.');
    }

    public function toggleFoodFeatured(FoodItem $foodItem)
    {
        $this->ensureFoodItemInScope($foodItem);
        $foodItem->update(['is_featured' => ! $foodItem->is_featured]);

        return back()->with('success', $foodItem->is_featured ? 'Food item marked as featured.' : 'Food item removed from featured.');
    }
}
