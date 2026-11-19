@extends('layouts.admin')@section('page-title','Payment #'.$payment->id)@section('content')
<div class="card card-soft"><div class="card-body">
<p>Order: #{{ $payment->order->order_number }}</p><p>Amount: {{ money_inr($payment->amount) }}</p><p>Gateway: {{ $payment->gateway ?? '-' }}</p>
@if($payment->method==='cod' && $payment->cod_collection_status!=='collected')
<form method="POST" action="{{ route('admin.payments.cod-collect',$payment) }}">@csrf
<input type="number" step="0.01" name="collected_amount" class="form-control mb-2" value="{{ $payment->amount }}" required>
<button class="btn btn-success btn-sm">Mark COD Collected</button></form>@endif
</div></div>@endsection
