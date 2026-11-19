@extends('layouts.admin')@section('page-title','Add Banner')@section('content')@include('admin.banners.form',['action'=>route('admin.banners.store')])@endsection
