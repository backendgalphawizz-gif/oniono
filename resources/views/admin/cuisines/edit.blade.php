@extends('layouts.admin')
@section('page-title', 'Edit Cuisine')
@section('content')
@include('admin.cuisines.form', [
    'action' => route('admin.cuisines.update', $cuisine),
    'method' => 'PUT',
    'cuisine' => $cuisine,
])
@endsection
