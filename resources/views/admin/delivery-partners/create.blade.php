@extends('layouts.admin')
@section('page-title', 'Add Delivery Partner')
@section('content')
@include('admin.delivery-partners._form', [
    'action' => route('admin.delivery-partners.store'),
    'partner' => null,
])
@endsection
