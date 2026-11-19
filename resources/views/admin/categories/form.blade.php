@php($category = $category ?? null)
<x-panel-form-card :title="$category ? 'Edit category' : 'New category'" subtitle="Main menu category (e.g. North Indian, Fast Food). Add sub-categories separately.">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @if (! empty($method))
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-name">Category name</label>
                <input name="name" id="field-name" class="form-control" value="{{ old('name', $category?->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-sort">Sort order</label>
                <input type="number" name="sort_order" id="field-sort" class="form-control" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" min="0">
            </div>
            <div class="col-12">
                <label class="form-label" for="field-image">Image</label>
                <input type="file" name="image" id="field-image" class="form-control">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="field-active" value="1" @checked(old('is_active', $category?->is_active ?? true))>
                    <label class="form-check-label" for="field-active">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">Save category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
