@extends('layouts.admin')@section('page-title',$settlement->settlement_number)@section('content')
<div class="card card-soft"><div class="card-body">
<p>Restaurant: {{ $settlement->restaurant->name }}</p><p>Amount: {{ money_inr($settlement->amount) }}</p><p>Status: {{ $settlement->status->value ?? $settlement->status }}</p>
<ul>@foreach($settlement->items as $item)<li>Order #{{ $item->earning->order->order_number }} — {{ money_inr($item->amount) }}</li>@endforeach</ul>
</div></div>@endsection
