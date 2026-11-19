@extends('layouts.admin')
@section('page-title', 'Add Cuisine')
@section('content')
@include('admin.cuisines.form', [
    'action' => route('admin.cuisines.store'),
    'cuisine' => null,
])
@endsection
