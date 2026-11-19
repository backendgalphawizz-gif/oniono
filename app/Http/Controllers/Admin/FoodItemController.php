<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesAdminGeoScope;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoodItem;
use App\Models\Restaurant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FoodItemController extends Controller
{
    use AppliesAdminGeoScope;

    public function index(Request $request)
    {
        $items = $this->scopeFoodItems(FoodItem::query())
            ->with(['restaurant', 'category.parent'])
            ->when($request->filled('restaurant_id'), fn ($q) => $q->where('restaurant_id', $request->restaurant_id))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->featured === '1', fn ($q) => $q->where('is_featured', true))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->trim();
                $q->where('name', 'like', "%{$s}%");
            })
            ->latest()
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        $restaurants = $this->scopeRestaurants(Restaurant::query())->orderBy('name')->get(['id', 'name']);
        $categories = Category::menuOptions();

        return view('admin.food-items.index', compact('items', 'restaurants', 'categories'));
    }

    public function create()
    {
        $restaurants = $this->scopeRestaurants(Restaurant::query())->orderBy('name')->get();
        $categories = Category::menuOptions();

        return view('admin.food-items.create', compact('restaurants', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);

        $restaurant = Restaurant::query()->findOrFail($data['restaurant_id']);
        $this->ensureRestaurantInScope($restaurant);

        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        $data['is_available'] = $request->boolean('is_available', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_sold_out'] = $request->boolean('is_sold_out');
        $data['is_gst_applicable'] = $request->boolean('is_gst_applicable');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('food', 'public');
        }

        $item = FoodItem::query()->create($data);

        return redirect()->route('admin.food-items.show', $item)->with('success', 'Food item created.');
    }

    public function show(FoodItem $foodItem)
    {
        $this->ensureFoodItemInScope($foodItem);
        $foodItem->load(['restaurant', 'category.parent', 'variants', 'addons']);
        $canEditPrice = $this->canEditPrices();

        return view('admin.food-items.show', compact('foodItem', 'canEditPrice'));
    }

    public function edit(FoodItem $foodItem)
    {
        $this->ensureFoodItemInScope($foodItem);
        $restaurants = $this->scopeRestaurants(Restaurant::query())->orderBy('name')->get();
        $categories = Category::menuOptions();
        $canEditPrice = $this->canEditPrices();

        return view('admin.food-items.edit', compact('foodItem', 'restaurants', 'categories', 'canEditPrice'));
    }

    public function update(Request $request, FoodItem $foodItem)
    {
        $this->ensureFoodItemInScope($foodItem);
        $canEditPrice = $this->canEditPrices();
        $data = $this->validateItem($request, $foodItem, $canEditPrice);

        $restaurant = Restaurant::query()->findOrFail($data['restaurant_id']);
        $this->ensureRestaurantInScope($restaurant);

        $data['is_available'] = $request->boolean('is_available');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_sold_out'] = $request->boolean('is_sold_out');
        $data['is_gst_applicable'] = $request->boolean('is_gst_applicable');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('food', 'public');
        }

        $foodItem->update($data);

        return redirect()->route('admin.food-items.show', $foodItem)->with('success', 'Food item updated.');
    }

    public function destroy(FoodItem $foodItem)
    {
        $this->ensureFoodItemInScope($foodItem);
        $foodItem->delete();

        return redirect()->route('admin.food-items.index')->with('success', 'Food item deleted.');
    }

    private function canEditPrices(): bool
    {
        return (bool) Setting::getValue('admin_food_price_editing', true);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateItem(Request $request, ?FoodItem $item = null, bool $canEditPrice = true): array
    {
        $rules = [
            'restaurant_id' => ['required', 'exists:restaurants,id'],
            'category_id' => ['required', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'food_type' => ['required', Rule::in(['veg', 'non_veg', 'egg'])],
            'preparation_time' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_gst_applicable' => ['nullable', 'boolean'],
            'gst_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        if ($canEditPrice) {
            $rules['price'] = ['required', 'numeric', 'min:0'];
        }

        return $request->validate($rules);
    }
}
