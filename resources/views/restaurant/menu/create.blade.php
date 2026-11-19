@extends('layouts.restaurant')

@section('page-title', 'Add menu item')

@section('content')
@include('restaurant.menu.form', [
    'action' => route('restaurant.menu.store'),
    'categories' => $categories,
])
@endsection
