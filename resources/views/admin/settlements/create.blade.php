@extends('layouts.admin')@section('page-title','Settle '.$restaurant->name)@section('content')
<form method="POST" action="{{ route('admin.settlements.store',$restaurant) }}">@csrf
<div class="card card-soft"><div class="card-body">
@forelse($earnings as $earning)
<div class="form-check mb-2"><input class="form-check-input earning-check" type="checkbox" name="earning_ids[]" value="{{ $earning->id }}" data-amount="{{ $earning->net_earning }}"><label class="form-check-label">Order #{{ $earning->order->order_number }} — {{ money_inr($earning->net_earning) }}</label></div>
@empty<p>No pending earnings.</p>@endforelse
<div class="row g-2 mt-3"><div class="col-md-3"><input type="number" step="0.01" name="amount" id="settlementAmount" class="form-control" required></div>
<div class="col-md-3"><input name="payment_mode" class="form-control" placeholder="Payment mode"></div>
<div class="col-md-3"><input name="transaction_reference" class="form-control" placeholder="Reference"></div></div>
<button class="btn btn-success mt-3">Confirm Settlement</button></div></div></form>
@push('scripts')<script>document.querySelectorAll('.earning-check').forEach(cb=>cb.addEventListener('change',()=>{let t=0;document.querySelectorAll('.earning-check:checked').forEach(x=>t+=parseFloat(x.dataset.amount));document.getElementById('settlementAmount').value=t.toFixed(2);}));</script>@endpush
@endsection
