@extends('layouts.admin')
@section('page-title', 'Add staff member')
@section('content')
@include('admin.settings.employees.partials.form', [
    'action' => route('admin.settings.employees.store'),
    'admin' => null,
    'permissionMatrix' => $permissionMatrix,
])
@endsection
