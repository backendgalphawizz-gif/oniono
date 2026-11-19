@extends('layouts.admin')
@section('page-title', 'Edit User')
@section('content')
@include('admin.users._form', [
    'action' => route('admin.users.update', $user),
    'method' => 'PUT',
    'user' => $user,
])
@endsection
