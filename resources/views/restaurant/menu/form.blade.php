@php
    $foodItem = $foodItem ?? null;
    $variants = old('variants', $foodItem?->variants?->map(fn ($v) => [
        'name' => $v->name,
        'price' => $v->price,
        'is_default' => $v->is_default,
    ])->all() ?? [['name' => '', 'price' => '', 'is_default' => true]]);
    $addons = old('addons', $foodItem?->addons?->map(fn ($a) => [
        'name' => $a->name,
        'price' => $a->price,
        'is_available' => $a->is_available,
    ])->all() ?? [['name' => '', 'price' => '', 'is_available' => true]]);
@endphp

<x-panel-form-card :title="$foodItem ? 'Edit menu item' : 'Add menu item'" subtitle="Description, pricing, variants, add-ons, and availability.">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form" id="menu-item-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="name">Item name</label>
                <input name="name" id="name" class="form-control" value="{{ old('name', $foodItem?->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="food_type">Food type</label>
                <select name="food_type" id="food_type" class="form-select" required>
                    @foreach (['veg' => 'Veg', 'non_veg' => 'Non-veg', 'vegan' => 'Vegan'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('food_type', $foodItem?->food_type ?? 'veg') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="category_id">Category</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    @forelse ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $foodItem?->category_id) == $category->id)>
                            {{ $category->displayLabel() }}
                            @if ($category->restaurant_id)
                                (Yours)
                            @endif
                        </option>
                    @empty
                        <option value="" disabled selected>No categories — add one under Menu → Categories</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="price">Base price (₹)</label>
                <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="{{ old('price', $foodItem?->price) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="preparation_time">Prep time (minutes)</label>
                <input type="number" min="1" max="600" name="preparation_time" id="preparation_time" class="form-control" value="{{ old('preparation_time', $foodItem?->preparation_time ?? 15) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $foodItem?->description) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="image">Item image</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                @if ($foodItem?->image)
                    <div class="mt-2 d-flex align-items-center gap-3">
                        <img src="{{ asset('storage/'.$foodItem->image) }}" alt="" class="rounded border" width="72" height="72" style="object-fit: cover;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                            <label class="form-check-label small" for="remove_image">Remove image</label>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <p class="form-label mb-2">Availability</p>
                <input type="hidden" name="is_available" value="0">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" @checked(old('is_available', $foodItem?->is_available ?? true))>
                    <label class="form-check-label" for="is_available">Available for ordering</label>
                </div>
                <input type="hidden" name="is_sold_out" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_sold_out" id="is_sold_out" value="1" @checked(old('is_sold_out', $foodItem?->is_sold_out))>
                    <label class="form-check-label" for="is_sold_out">Mark as sold out</label>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 mb-0">Variants</h3>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-row="variants">Add variant</button>
        </div>
        <p class="small text-muted">Optional sizes or styles (e.g. Regular, Large). One can be default.</p>
        <div id="variants-list" class="d-flex flex-column gap-2 mb-4">
            @foreach ($variants as $i => $variant)
                <div class="row g-2 align-items-end menu-repeater-row" data-repeater="variants">
                    <div class="col-md-4">
                        <input name="variants[{{ $i }}][name]" class="form-control form-control-sm" placeholder="Variant name" value="{{ $variant['name'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" min="0" name="variants[{{ $i }}][price]" class="form-control form-control-sm" placeholder="Price" value="{{ $variant['price'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input variant-default" type="checkbox" name="variants[{{ $i }}][is_default]" value="1" @checked(! empty($variant['is_default']))>
                            <label class="form-check-label small">Default</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 mb-0">Add-ons</h3>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-row="addons">Add add-on</button>
        </div>
        <p class="small text-muted">Optional extras (e.g. extra cheese, dip).</p>
        <div id="addons-list" class="d-flex flex-column gap-2 mb-4">
            @foreach ($addons as $i => $addon)
                <div class="row g-2 align-items-end menu-repeater-row" data-repeater="addons">
                    <div class="col-md-4">
                        <input name="addons[{{ $i }}][name]" class="form-control form-control-sm" placeholder="Add-on name" value="{{ $addon['name'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" min="0" name="addons[{{ $i }}][price]" class="form-control form-control-sm" placeholder="Price" value="{{ $addon['price'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="addons[{{ $i }}][is_available]" value="1" @checked($addon['is_available'] ?? true)>
                            <label class="form-check-label small">Available</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-brand">{{ $foodItem ? 'Save changes' : 'Create item' }}</button>
            <a href="{{ route('restaurant.menu.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>

@push('scripts')
<script>
(function () {
    var variantIndex = {{ count($variants) }};
    var addonIndex = {{ count($addons) }};

    function bindRemove(row) {
        row.querySelector('[data-remove-row]')?.addEventListener('click', function () {
            row.remove();
        });
    }

    document.querySelectorAll('.menu-repeater-row').forEach(bindRemove);

    document.querySelectorAll('[data-add-row]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var type = btn.getAttribute('data-add-row');
            var list = document.getElementById(type + '-list');
            var idx = type === 'variants' ? variantIndex++ : addonIndex++;
            var row = document.createElement('div');
            row.className = 'row g-2 align-items-end menu-repeater-row';
            row.setAttribute('data-repeater', type);

            if (type === 'variants') {
                row.innerHTML =
                    '<div class="col-md-4"><input name="variants[' + idx + '][name]" class="form-control form-control-sm" placeholder="Variant name"></div>' +
                    '<div class="col-md-3"><input type="number" step="0.01" min="0" name="variants[' + idx + '][price]" class="form-control form-control-sm" placeholder="Price"></div>' +
                    '<div class="col-md-3"><div class="form-check"><input class="form-check-input variant-default" type="checkbox" name="variants[' + idx + '][is_default]" value="1"><label class="form-check-label small">Default</label></div></div>' +
                    '<div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>';
            } else {
                row.innerHTML =
                    '<div class="col-md-4"><input name="addons[' + idx + '][name]" class="form-control form-control-sm" placeholder="Add-on name"></div>' +
                    '<div class="col-md-3"><input type="number" step="0.01" min="0" name="addons[' + idx + '][price]" class="form-control form-control-sm" placeholder="Price"></div>' +
                    '<div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="addons[' + idx + '][is_available]" value="1" checked><label class="form-check-label small">Available</label></div></div>' +
                    '<div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>';
            }

            list.appendChild(row);
            bindRemove(row);
        });
    });

    document.getElementById('is_sold_out')?.addEventListener('change', function () {
        if (this.checked) {
            document.getElementById('is_available').checked = false;
        }
    });
})();
</script>
@endpush
