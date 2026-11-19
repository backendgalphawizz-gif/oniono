@extends('layouts.admin')
@section('page-title', 'Edit Sub-category')
@section('content')
@include('admin.sub-categories.form', [
    'action' => route('admin.sub-categories.update', $subCategory),
    'method' => 'PUT',
    'subCategory' => $subCategory,
    'mainCategories' => $mainCategories,
])
@endsection
