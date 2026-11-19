@extends('layouts.admin')
@section('page-title', 'Edit Restaurant')
@section('content')
@include('partials.restaurant-documents-display', [
    'restaurant' => $restaurant,
    'adminReview' => false,
])
@include('admin.restaurants._form', [
    'action' => route('admin.restaurants.update', $restaurant),
    'method' => 'PUT',
    'restaurant' => $restaurant,
    'masterCommission' => $masterCommission,
])
@endsection
