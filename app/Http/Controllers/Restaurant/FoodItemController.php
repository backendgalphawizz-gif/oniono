<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoodAddon;
use App\Models\FoodItem;
use App\Models\FoodVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FoodItemController extends Controller
{
    public function index(Request $request)
    {
        $items = FoodItem::query()
            ->forRestaurant(restaurant_id())
            ->with('category.parent')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('availability'), function ($q) use ($request) {
                match ($request->string('availability')->toString()) {
                    'available' => $q->where('is_available', true)->where('is_sold_out', false),
                    'unavailable' => $q->where('is_available', false),
                    'sold_out' => $q->where('is_sold_out', true),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::menuOptionsForRestaurant(restaurant_id());

        return view('restaurant.menu.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::menuOptionsForRestaurant(restaurant_id());

        return view('restaurant.menu.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);
        $this->assertCategoryAllowed($data['category_id']);

        $data['restaurant_id'] = restaurant_id();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('food', 'public');
        }

        $item = FoodItem::query()->create($data);
        $this->syncVariantsAddons($request, $item);

        return redirect()->route('restaurant.menu.index')->with('success', 'Menu item added.');
    }

    public function edit(FoodItem $foodItem)
    {
        $this->authorizeItem($foodItem);
        $categories = Category::menuOptionsForRestaurant(restaurant_id());
        $foodItem->load(['variants', 'addons']);

        return view('restaurant.menu.edit', compact('foodItem', 'categories'));
    }

    public function update(Request $request, FoodItem $foodItem)
    {
        $this->authorizeItem($foodItem);
        $data = $this->validateItem($request);
        $this->assertCategoryAllowed($data['category_id']);

        if ($request->boolean('remove_image') && $foodItem->image) {
            Storage::disk('public')->delete($foodItem->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($foodItem->image) {
                Storage::disk('public')->delete($foodItem->image);
            }
            $data['image'] = $request->file('image')->store('food', 'public');
        }

        unset($data['remove_image']);

        $foodItem->update($data);
        $foodItem->variants()->delete();
        $foodItem->addons()->delete();
        $this->syncVariantsAddons($request, $foodItem);

        return redirect()->route('restaurant.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(FoodItem $foodItem)
    {
        $this->authorizeItem($foodItem);

        if ($foodItem->image) {
            Storage::disk('public')->delete($foodItem->image);
        }

        $foodItem->delete();

        return back()->with('success', 'Menu item deleted.');
    }

    public function updateAvailability(Request $request, FoodItem $foodItem)
    {
        $this->authorizeItem($foodItem);

        $data = $request->validate([
            'is_available' => ['nullable', 'boolean'],
            'is_sold_out' => ['nullable', 'boolean'],
        ]);

        if ($request->has('is_available')) {
            $foodItem->is_available = $request->boolean('is_available');
        }
        if ($request->has('is_sold_out')) {
            $foodItem->is_sold_out = $request->boolean('is_sold_out');
        }

        $foodItem->save();

        return back()->with('success', 'Item availability updated.');
    }

    private function validateItem(Request $request): array
    {
        $request->validate([
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['nullable', 'string', 'max:100'],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons.*.is_available' => ['nullable', 'boolean'],
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'food_type' => ['required', 'in:veg,non_veg,vegan'],
            'preparation_time' => ['nullable', 'integer', 'min:1', 'max:600'],
            'image' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $data['preparation_time'] = $data['preparation_time'] ?? 15;
        $data['is_available'] = $request->boolean('is_available');
        $data['is_sold_out'] = $request->boolean('is_sold_out');
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['is_sold_out']) {
            $data['is_available'] = false;
        }

        return $data;
    }

    private function syncVariantsAddons(Request $request, FoodItem $item): void
    {
        $defaultSet = false;

        foreach ($request->input('variants', []) as $variant) {
            if (empty($variant['name'])) {
                continue;
            }

            $isDefault = ! empty($variant['is_default']) && ! $defaultSet;
            if ($isDefault) {
                $defaultSet = true;
            }

            FoodVariant::query()->create([
                'food_item_id' => $item->id,
                'name' => $variant['name'],
                'price' => $variant['price'] ?? $item->price,
                'is_default' => $isDefault,
            ]);
        }

        foreach ($request->input('addons', []) as $addon) {
            if (empty($addon['name'])) {
                continue;
            }

            FoodAddon::query()->create([
                'food_item_id' => $item->id,
                'name' => $addon['name'],
                'price' => $addon['price'] ?? 0,
                'is_available' => ! array_key_exists('is_available', $addon) || ! empty($addon['is_available']),
            ]);
        }
    }

    private function assertCategoryAllowed(int $categoryId): void
    {
        $allowed = Category::menuOptionsForRestaurant(restaurant_id())->pluck('id');

        abort_unless($allowed->contains($categoryId), 422, 'Invalid category for your restaurant.');
    }

    private function authorizeItem(FoodItem $foodItem): void
    {
        abort_if($foodItem->restaurant_id !== restaurant_id(), 403);
    }
}
