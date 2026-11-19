@extends('layouts.admin')
@section('page-title', 'Edit Food Item')
@section('content')
@include('admin.food-items._form', [
    'action' => route('admin.food-items.update', $foodItem),
    'method' => 'PUT',
    'foodItem' => $foodItem,
    'restaurants' => $restaurants,
    'categories' => $categories,
    'canEditPrice' => $canEditPrice,
])
@endsection
