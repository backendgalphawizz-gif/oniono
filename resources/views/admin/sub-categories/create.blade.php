@extends('layouts.admin')
@section('page-title', 'Add Sub-category')
@section('content')
@if ($mainCategories->isEmpty())
    <div class="alert alert-warning">
        Create at least one <a href="{{ route('admin.categories.create') }}">main category</a> before adding sub-categories.
    </div>
@endif
@include('admin.sub-categories.form', [
    'action' => route('admin.sub-categories.store'),
    'subCategory' => null,
    'mainCategories' => $mainCategories,
])
@endsection
