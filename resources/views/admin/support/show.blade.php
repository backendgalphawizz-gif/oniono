@extends('layouts.admin')@section('page-title',$support->ticket_number)@section('content')
<div class="card card-soft mb-3"><div class="card-body">@foreach($support->messages as $msg)<div class="mb-2"><strong>{{ $msg->sender_type }}</strong><div>{{ $msg->message }}</div></div>@endforeach</div></div>
<form method="POST" action="{{ route('admin.support.reply',$support) }}">@csrf<textarea name="message" class="form-control mb-2" required></textarea><button class="btn btn-success">Reply</button></form>@endsection
