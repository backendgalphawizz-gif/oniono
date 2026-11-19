@extends('layouts.admin')
@section('page-title', 'Edit staff member')
@section('title', 'Edit staff — '.$admin->name)
@section('content')
@include('admin.settings.employees.partials.form', [
    'action' => route('admin.settings.employees.update', $admin),
    'admin' => $admin,
    'permissionMatrix' => $permissionMatrix,
])
@endsection
