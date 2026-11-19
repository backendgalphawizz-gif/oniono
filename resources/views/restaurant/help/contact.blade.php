@extends('layouts.restaurant')

@section('page-title', 'Contact support')

@section('content')
<div class="mb-3">
    <h2 class="h5 mb-1">Contact support team</h2>
    <p class="text-muted small mb-0">Reach {{ $contact['platform_name'] }} for partner onboarding, payouts, and technical help.</p>
</div>

@include('restaurant.help.partials.subnav')

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Direct contact</h3>
                <ul class="list-unstyled small mb-4">
                    @if ($contact['support_email'])
                        <li class="mb-3">
                            <div class="text-muted">Support email</div>
                            <a href="mailto:{{ $contact['support_email'] }}">{{ $contact['support_email'] }}</a>
                        </li>
                    @endif
                    @if ($contact['site_phone'])
                        <li class="mb-3">
                            <div class="text-muted">Phone</div>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contact['site_phone']) }}">{{ $contact['site_phone'] }}</a>
                        </li>
                    @endif
                    @if ($contact['site_email'] && $contact['site_email'] !== $contact['support_email'])
                        <li class="mb-3">
                            <div class="text-muted">General inquiries</div>
                            <a href="mailto:{{ $contact['site_email'] }}">{{ $contact['site_email'] }}</a>
                        </li>
                    @endif
                    @if ($contact['site_address'])
                        <li class="mb-3">
                            <div class="text-muted">Office address</div>
                            <div class="text-body">{!! nl2br(e($contact['site_address'])) !!}</div>
                        </li>
                    @endif
                </ul>
                @if (! $contact['support_email'] && ! $contact['site_phone'] && ! $contact['site_address'])
                    <p class="text-muted small">Contact details are not configured yet. Please raise a support ticket instead.</p>
                @endif
                <a href="{{ route('restaurant.support.index') }}#new-ticket" class="btn btn-brand btn-sm">
                    <i class="bi bi-ticket-detailed me-1"></i> Open a support ticket
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h3 class="h6 mb-2">When to use tickets vs email</h3>
                <p class="small text-muted">Tickets keep a record tied to your restaurant account and are best for order disputes, settlement questions, and document verification.</p>
                <p class="small text-muted mb-3">Use email or phone for urgent outages or if you cannot sign in.</p>
                <a href="{{ route('restaurant.help.page', 'contact-us') }}" class="btn btn-outline-secondary btn-sm">
                    View full Contact Us page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
