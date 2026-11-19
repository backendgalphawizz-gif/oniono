@php($subCategory = $subCategory ?? null)
<x-panel-form-card :title="$subCategory ? 'Edit sub-category' : 'New sub-category'" subtitle="Sub-category is always linked to one main category.">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-parent">Main category</label>
                <select name="parent_id" id="field-parent" class="form-select" required>
                    <option value="">Select main category</option>
                    @foreach ($mainCategories as $main)
                        <option value="{{ $main->id }}" @selected(old('parent_id', $subCategory?->parent_id) == $main->id)>{{ $main->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-name">Sub-category name</label>
                <input name="name" id="field-name" class="form-control" value="{{ old('name', $subCategory?->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-sort">Sort order</label>
                <input type="number" name="sort_order" id="field-sort" class="form-control" value="{{ old('sort_order', $subCategory?->sort_order ?? 0) }}" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-image">Image</label>
                <input type="file" name="image" id="field-image" class="form-control">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="field-active" value="1" @checked(old('is_active', $subCategory?->is_active ?? true))>
                    <label class="form-check-label" for="field-active">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">Save sub-category</button>
            <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
