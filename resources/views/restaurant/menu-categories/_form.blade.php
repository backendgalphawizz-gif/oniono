@php
    $menuCategory = $menuCategory ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="parent_id">Main category</label>
        <select name="parent_id" id="parent_id" class="form-select" required>
            @foreach ($mainCategories as $main)
                <option value="{{ $main->id }}" @selected(old('parent_id', $menuCategory?->parent_id) == $main->id)>{{ $main->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">Your sub-category name</label>
        <input name="name" id="name" class="form-control" value="{{ old('name', $menuCategory?->name) }}" required placeholder="e.g. Chef specials">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="sort_order">Sort order</label>
        <input type="number" min="0" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $menuCategory?->sort_order ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="image">Category image</label>
        <input type="file" name="image" id="image" class="form-control" accept="image/*">
    </div>
    @if ($menuCategory)
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $menuCategory->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    @endif
</div>
