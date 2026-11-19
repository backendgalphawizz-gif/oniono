@extends('layouts.restaurant')

@section('page-title', 'Edit menu item')

@section('content')
@include('restaurant.menu.form', [
    'action' => route('restaurant.menu.update', $foodItem),
    'method' => 'PUT',
    'foodItem' => $foodItem,
    'categories' => $categories,
])
@endsection
