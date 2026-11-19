@extends('layouts.admin')
@section('page-title', 'Add Category')
@section('content')
@include('admin.categories.form', [
    'action' => route('admin.categories.store'),
    'category' => null,
])
@endsection
