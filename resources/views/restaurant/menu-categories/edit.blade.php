@extends('layouts.restaurant')

@section('page-title', 'Edit category')

@section('content')
<x-panel-form-card title="Edit sub-category">
    <form method="POST" action="{{ route('restaurant.menu-categories.update', $menuCategory) }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @method('PUT')
        @include('restaurant.menu-categories._form', ['mainCategories' => $mainCategories, 'menuCategory' => $menuCategory])
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-brand">Save changes</button>
            <a href="{{ route('restaurant.menu-categories.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
@endsection
