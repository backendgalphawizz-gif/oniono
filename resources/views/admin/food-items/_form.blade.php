<x-panel-form-card :title="$foodItem ? 'Edit food listing' : 'New food listing'" subtitle="Menu item details and availability">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-restaurant">Restaurant</label>
                <select name="restaurant_id" id="field-restaurant" class="form-select" required>
                    @foreach ($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" @selected(old('restaurant_id', $foodItem?->restaurant_id) == $restaurant->id)>{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-category">Sub-category</label>
                <select name="category_id" id="field-category" class="form-select" required>
                    @forelse ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $foodItem?->category_id) == $category->id)>{{ $category->displayLabel() }}</option>
                    @empty
                        <option value="" disabled selected>No sub-categories — create main category &amp; sub-category first</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="field-name">Item name</label>
                <input type="text" name="name" id="field-name" class="form-control" value="{{ old('name', $foodItem?->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-food-type">Food type</label>
                <select name="food_type" id="field-food-type" class="form-select" required>
                    @foreach (['veg' => 'Veg', 'non_veg' => 'Non-veg', 'egg' => 'Egg'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('food_type', $foodItem?->food_type ?? 'veg') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-price">
                    Price (₹)
                    @if (! ($canEditPrice ?? true))
                        <span class="text-muted fw-normal">— editing disabled</span>
                    @endif
                </label>
                @if ($canEditPrice ?? true)
                    <input type="number" step="0.01" min="0" name="price" id="field-price" class="form-control" value="{{ old('price', $foodItem?->price) }}" required>
                @else
                    <input type="text" class="form-control" value="{{ money_inr($foodItem?->price ?? 0) }}" disabled>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-prep">Prep time (mins)</label>
                <input type="number" min="0" name="preparation_time" id="field-prep" class="form-control" value="{{ old('preparation_time', $foodItem?->preparation_time) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-gst-percent">Item GST % <span class="text-muted fw-normal">(optional)</span></label>
                <input type="number" step="0.01" min="0" max="100" name="gst_percentage" id="field-gst-percent" class="form-control" value="{{ old('gst_percentage', $foodItem?->gst_percentage) }}" placeholder="Restaurant / master default">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="field-image">Image</label>
                <input type="file" name="image" id="field-image" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label" for="field-description">Description</label>
                <textarea name="description" id="field-description" class="form-control" rows="3">{{ old('description', $foodItem?->description) }}</textarea>
            </div>
            <div class="col-12 d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_available" id="field-available" value="1" @checked(old('is_available', $foodItem?->is_available ?? true))>
                    <label class="form-check-label" for="field-available">Available</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_featured" id="field-featured" value="1" @checked(old('is_featured', $foodItem?->is_featured))>
                    <label class="form-check-label" for="field-featured">Featured</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_sold_out" id="field-sold-out" value="1" @checked(old('is_sold_out', $foodItem?->is_sold_out))>
                    <label class="form-check-label" for="field-sold-out">Sold out</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_gst_applicable" id="field-gst" value="1" @checked(old('is_gst_applicable', $foodItem?->is_gst_applicable ?? false))>
                    <label class="form-check-label" for="field-gst">Price includes GST (with GST)</label>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">{{ $foodItem ? 'Save changes' : 'Create item' }}</button>
            <a href="{{ $foodItem ? route('admin.food-items.show', $foodItem) : route('admin.food-items.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
