<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $restaurantId = restaurant_id();

        $platformCategories = Category::query()
            ->sub()
            ->with('parent')
            ->whereNull('restaurant_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $myCategories = Category::query()
            ->sub()
            ->with('parent')
            ->ownedByRestaurant($restaurantId)
            ->withCount(['foodItems' => fn ($q) => $q->where('restaurant_id', $restaurantId)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $mainCategories = Category::query()->main()->where('is_active', true)->orderBy('name')->get();

        return view('restaurant.menu-categories.index', compact('platformCategories', 'myCategories', 'mainCategories'));
    }

    public function create()
    {
        $mainCategories = Category::query()->main()->where('is_active', true)->orderBy('name')->get();

        return view('restaurant.menu-categories.create', compact('mainCategories'));
    }

    public function store(Request $request)
    {
        $restaurantId = restaurant_id();

        $data = $request->validate([
            'parent_id' => ['required', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['restaurant_id'] = $restaurantId;
        $data['slug'] = Category::generateUniqueSlug($data['name'], (int) $data['parent_id']);
        $data['is_active'] = true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::query()->create($data);

        return redirect()->route('restaurant.menu-categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $menuCategory)
    {
        $this->authorizeCategory($menuCategory);
        $mainCategories = Category::query()->main()->where('is_active', true)->orderBy('name')->get();

        return view('restaurant.menu-categories.edit', compact('menuCategory', 'mainCategories'));
    }

    public function update(Request $request, Category $menuCategory)
    {
        $this->authorizeCategory($menuCategory);

        $data = $request->validate([
            'parent_id' => ['required', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['slug'] = Category::generateUniqueSlug($data['name'], (int) $data['parent_id'], $menuCategory->id);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($menuCategory->image) {
                Storage::disk('public')->delete($menuCategory->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $menuCategory->update($data);

        return redirect()->route('restaurant.menu-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $menuCategory)
    {
        $this->authorizeCategory($menuCategory);

        $inUse = FoodItem::query()
            ->where('restaurant_id', restaurant_id())
            ->where('category_id', $menuCategory->id)
            ->exists();

        if ($inUse) {
            return back()->withErrors(['category' => 'Reassign or delete menu items in this category first.']);
        }

        if ($menuCategory->image) {
            Storage::disk('public')->delete($menuCategory->image);
        }

        $menuCategory->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function authorizeCategory(Category $category): void
    {
        abort_unless($category->isSub(), 404);
        abort_if($category->restaurant_id !== restaurant_id(), 403);
    }
}
