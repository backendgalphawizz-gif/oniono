@extends('layouts.admin')
@section('page-title', 'Edit Category')
@section('content')
@include('admin.categories.form', [
    'action' => route('admin.categories.update', $category),
    'method' => 'PUT',
    'category' => $category,
])
@endsection
