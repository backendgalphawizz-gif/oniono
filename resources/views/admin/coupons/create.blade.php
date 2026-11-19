@extends('layouts.admin')@section('page-title','Add Coupon')@section('content')@include('admin.coupons.form',['action'=>route('admin.coupons.store')])@endsection
