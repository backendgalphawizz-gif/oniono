@extends('layouts.admin')
@section('page-title', 'Add Restaurant')
@section('content')
@include('admin.restaurants._form', ['action' => route('admin.restaurants.store'), 'restaurant' => null])
@endsection
