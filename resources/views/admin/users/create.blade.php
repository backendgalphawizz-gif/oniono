@extends('layouts.admin')
@section('page-title', 'Add User')
@section('content')
@include('admin.users._form', [
    'action' => route('admin.users.store'),
    'user' => null,
])
@endsection
