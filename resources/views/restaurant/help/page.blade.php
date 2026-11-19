@extends('layouts.restaurant')

@section('page-title', $page->title)

@section('content')
<div class="mb-3">
    <h2 class="h5 mb-1">{{ $page->title }}</h2>
    @if (! empty($definition['nav_label']) && $definition['nav_label'] !== $page->title)
        <p class="text-muted small mb-0">{{ $definition['nav_label'] }}</p>
    @endif
</div>

@include('restaurant.help.partials.subnav')

<div class="card card-soft">
    <div class="card-body cms-static-content">
        @if ($page->is_active)
            {!! $page->content !!}
        @else
            <p class="text-muted mb-0">This page is temporarily unavailable. Please contact support if you need assistance.</p>
        @endif
    </div>
</div>

<p class="small text-muted mt-3 mb-0">
    <a href="{{ route('restaurant.help.index') }}"><i class="bi bi-arrow-left"></i> Back to Support &amp; help</a>
</p>
@endsection
