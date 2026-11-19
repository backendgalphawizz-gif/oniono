@extends('layouts.restaurant')@section('page-title','Timings')@section('content')
<div class="card card-soft"><div class="card-body"><form method="POST" action="{{ route('restaurant.timings.update') }}">@csrf @method('PUT')
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_open" value="1" @checked($restaurant->is_open)><label class="form-check-label">Restaurant Open</label></div>
@foreach(range(0,6) as $day)
@php $t = $timings->get($day); @endphp
<div class="row g-2 mb-2 align-items-center"><div class="col-md-2">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$day] }}</div>
<div class="col-md-3"><input type="time" name="timings[{{ $day }}][opens_at]" class="form-control" value="{{ $t?->opens_at ? \Illuminate\Support\Str::of($t->opens_at)->substr(0,5) : '' }}"></div>
<div class="col-md-3"><input type="time" name="timings[{{ $day }}][closes_at]" class="form-control" value="{{ $t?->closes_at ? \Illuminate\Support\Str::of($t->closes_at)->substr(0,5) : '' }}"></div>
<div class="col-md-2 form-check"><input class="form-check-input" type="checkbox" name="timings[{{ $day }}][is_closed]" value="1" @checked($t?->is_closed)><label class="form-check-label">Closed</label></div></div>
@endforeach
<hr><div class="row g-2"><div class="col-md-4"><input type="date" name="holiday_date" class="form-control"></div><div class="col-md-4"><input name="holiday_reason" class="form-control" placeholder="Holiday reason"></div></div>
<button class="btn btn-success mt-3">Save Timings</button></form></div></div>@endsection
