@extends('layouts.admin')
@section('page-title', 'Add Food Item')
@section('content')
@include('admin.food-items._form', [
    'action' => route('admin.food-items.store'),
    'foodItem' => null,
    'restaurants' => $restaurants,
    'categories' => $categories,
    'canEditPrice' => true,
])
@endsection
