<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategories = Category::query()
            ->sub()
            ->with('parent')
            ->withCount('foodItems')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->trim();
                $q->where(function ($inner) use ($s) {
                    $inner->where('name', 'like', "%{$s}%")
                        ->orWhereHas('parent', fn ($p) => $p->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', $request->parent_id))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('sort_order')
            ->paginate(admin_listing_per_page())
            ->withQueryString();

        $mainCategories = Category::query()->main()->orderBy('name')->get(['id', 'name']);

        return view('admin.sub-categories.index', compact('subCategories', 'mainCategories'));
    }

    public function create()
    {
        $mainCategories = Category::query()->main()->where('is_active', true)->orderBy('name')->get();

        return view('admin.sub-categories.create', compact('mainCategories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->assertMainParent((int) $data['parent_id']);

        $data['slug'] = Category::generateUniqueSlug($data['name'], (int) $data['parent_id']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::query()->create($data);

        return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-category created.');
    }

    public function edit(Category $subCategory)
    {
        $this->assertSubCategory($subCategory);

        $mainCategories = Category::query()->main()->orderBy('name')->get();

        return view('admin.sub-categories.edit', compact('subCategory', 'mainCategories'));
    }

    public function update(Request $request, Category $subCategory)
    {
        $this->assertSubCategory($subCategory);

        $data = $this->validateData($request);
        $this->assertMainParent((int) $data['parent_id']);

        $data['slug'] = Category::generateUniqueSlug(
            $data['name'],
            (int) $data['parent_id'],
            $subCategory->id
        );
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $subCategory->update($data);

        return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-category updated.');
    }

    public function destroy(Category $subCategory)
    {
        $this->assertSubCategory($subCategory);
        $subCategory->delete();

        return back()->with('success', 'Sub-category deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['required', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function assertSubCategory(Category $category): void
    {
        abort_unless($category->isSub(), 404);
    }

    private function assertMainParent(int $parentId): void
    {
        $parent = Category::query()->find($parentId);
        abort_unless($parent && $parent->isMain(), 422, 'Sub-category must be linked to a main category.');
    }
}
