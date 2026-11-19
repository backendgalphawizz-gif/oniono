@extends('layouts.restaurant')

@section('page-title', 'Add category')

@section('content')
<x-panel-form-card title="New sub-category" subtitle="Create a menu category under a main platform group (e.g. Fast Food → Your specials).">
    <form method="POST" action="{{ route('restaurant.menu-categories.store') }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @include('restaurant.menu-categories._form', ['mainCategories' => $mainCategories])
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-brand">Create category</button>
            <a href="{{ route('restaurant.menu-categories.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>
@endsection
