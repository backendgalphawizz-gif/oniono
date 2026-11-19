@extends('layouts.restaurant')

@section('page-title', 'Profile')

@section('content')
@php
    $restaurantUser = auth('restaurant')->user();
    $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
@endphp

<div class="dashboard-intro mb-4">
    <h2>Profile management</h2>
    <p>Update your restaurant details, hours, documents, and availability. Changes apply only to your store.</p>
</div>

@if (session('success'))
    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<nav class="nav nav-pills flex-wrap gap-2 mb-4 profile-section-nav" aria-label="Profile sections">
    <a class="nav-link" href="#profile-details">Details &amp; photo</a>
    <a class="nav-link" href="#contact-address">Contact &amp; address</a>
    <a class="nav-link" href="#availability">Availability</a>
    <a class="nav-link" href="#opening-hours">Opening hours</a>
    <a class="nav-link" href="#documents">Documents</a>
</nav>

{{-- Details & profile photo --}}
<x-panel-form-card id="profile-details" title="Profile details" subtitle="Restaurant name, owner, description, and logo.">
    <form method="POST" action="{{ route('restaurant.profile.update') }}" enctype="multipart/form-data" class="panel-form">
        @csrf
        @method('PUT')

        <div class="row g-4 align-items-start mb-4">
            <div class="col-md-auto text-center">
                @if ($restaurant->logo)
                    <img src="{{ asset('storage/'.$restaurant->logo) }}" alt="" class="rounded-circle border shadow-sm profile-logo-preview" width="96" height="96">
                @else
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode($restaurant->name) }}&background=ff8c00&color=fff&size=192"
                        alt=""
                        class="rounded-circle border shadow-sm profile-logo-preview"
                        width="96"
                        height="96"
                    >
                @endif
                <p class="small text-muted mt-2 mb-0">Profile photo / logo</p>
            </div>
            <div class="col-md">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Restaurant name</label>
                        <input name="name" id="name" class="form-control" value="{{ old('name', $restaurant->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="owner_name">Owner / contact name</label>
                        <input name="owner_name" id="owner_name" class="form-control" value="{{ old('owner_name', $restaurant->owner_name) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $restaurant->description) }}</textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="logo">Upload new logo</label>
                        <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        @if ($restaurant->logo)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                                <label class="form-check-label small" for="remove_logo">Remove current logo</label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <h3 class="h6 text-uppercase text-muted mb-3" id="contact-address">Contact &amp; address</h3>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="email">Login email</label>
                <input type="email" id="email" class="form-control" value="{{ $restaurantUser->email }}" disabled>
                <div class="form-text">Contact support to change login email.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Mobile number</label>
                <input name="phone" id="phone" class="form-control" value="{{ old('phone', $restaurant->phone) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="address">Address</label>
                <input name="address" id="address" class="form-control" value="{{ old('address', $restaurant->address) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city">City</label>
                <input name="city" id="city" class="form-control" value="{{ old('city', $restaurant->city) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="state">State</label>
                <input name="state" id="state" class="form-control" value="{{ old('state', $restaurant->state) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="pincode">Pincode</label>
                <input name="pincode" id="pincode" class="form-control" value="{{ old('pincode', $restaurant->pincode) }}" required>
            </div>
        </div>

        <h3 class="h6 text-uppercase text-muted mb-3">Payout details (optional)</h3>
        <div class="row g-3 mb-4">
            @foreach (['bank_account_name' => 'Account holder', 'bank_name' => 'Bank name', 'bank_account_number' => 'Account number', 'bank_ifsc' => 'IFSC'] as $field => $label)
                <div class="col-md-6">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input name="{{ $field }}" id="{{ $field }}" class="form-control" value="{{ old($field, $restaurant->$field) }}">
                </div>
            @endforeach
        </div>

        <h3 class="h6 text-uppercase text-muted mb-3" id="documents">Business documents</h3>
        <p class="small text-muted">Replace a file to reset verification to pending.</p>
        <div class="row g-3 mb-4">
            @include('partials.restaurant-document-uploads', ['restaurant' => $restaurant, 'requireAll' => false])
        </div>

        @if (! empty($documentChecklist))
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Document</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($documentChecklist as $item)
                            <tr>
                                <td>{{ $item['label'] }}</td>
                                <td>
                                    @if (! $item['uploaded'])
                                        <span class="badge text-bg-secondary">Missing</span>
                                    @else
                                        <span class="badge text-bg-{{ $item['status'] === 'verified' ? 'success' : ($item['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($item['status'] ?? 'pending') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <button type="submit" class="btn btn-brand">Save profile &amp; documents</button>
    </form>
</x-panel-form-card>

{{-- Availability & opening hours --}}
<x-panel-form-card id="availability" title="Restaurant availability" subtitle="Control whether customers can place new orders right now.">
    <form method="POST" action="{{ route('restaurant.timings.update') }}" class="panel-form">
        @csrf
        @method('PUT')

        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <input type="hidden" name="is_open" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_open" id="is_open" value="1" @checked(old('is_open', $restaurant->is_open))>
                <label class="form-check-label fw-medium" for="is_open">Accepting orders (open)</label>
            </div>
            <span class="badge text-bg-{{ $restaurant->is_open ? 'success' : 'secondary' }}">
                {{ $restaurant->is_open ? 'Open' : 'Closed' }}
            </span>
            @if ($restaurant->is_blocked)
                <span class="badge text-bg-danger">Blocked by admin</span>
            @else
                <span class="badge text-bg-light border">Account: {{ $restaurant->accountStatusLabel() }}</span>
            @endif
        </div>
        <p class="small text-muted mb-0">
            When closed, your restaurant stays visible but customers cannot start new orders. Admin block overrides this setting.
        </p>
        <input type="hidden" name="from_availability" value="1">
        <button type="submit" class="btn btn-outline-brand btn-sm mt-3">Update availability only</button>
    </form>
</x-panel-form-card>

<x-panel-form-card id="opening-hours" title="Opening hours" subtitle="Weekly schedule and planned holidays.">
    <form method="POST" action="{{ route('restaurant.timings.update') }}" class="panel-form">
        @csrf
        @method('PUT')

        <input type="hidden" name="is_open" value="{{ $restaurant->is_open ? 1 : 0 }}">

        <div class="table-responsive mb-3">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Opens</th>
                        <th>Closes</th>
                        <th>Closed all day</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (range(0, 6) as $day)
                        @php $t = $timings->get($day); @endphp
                        <tr>
                            <td class="fw-medium">{{ $dayNames[$day] }}</td>
                            <td>
                                <input
                                    type="time"
                                    name="timings[{{ $day }}][opens_at]"
                                    class="form-control form-control-sm"
                                    value="{{ old("timings.$day.opens_at", $t?->opens_at ? \Illuminate\Support\Str::of($t->opens_at)->substr(0, 5) : '') }}"
                                >
                            </td>
                            <td>
                                <input
                                    type="time"
                                    name="timings[{{ $day }}][closes_at]"
                                    class="form-control form-control-sm"
                                    value="{{ old("timings.$day.closes_at", $t?->closes_at ? \Illuminate\Support\Str::of($t->closes_at)->substr(0, 5) : '') }}"
                                >
                            </td>
                            <td class="text-center">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="timings[{{ $day }}][is_closed]"
                                    value="1"
                                    @checked(old("timings.$day.is_closed", $t?->is_closed))
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="h6 mb-2">Add holiday</h3>
        <div class="row g-2 mb-4">
            <div class="col-md-4">
                <input type="date" name="holiday_date" class="form-control" min="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-5">
                <input name="holiday_reason" class="form-control" placeholder="Reason (optional)">
            </div>
        </div>

        @if ($holidays->isNotEmpty())
            <h3 class="h6 mb-2">Upcoming holidays</h3>
            <ul class="list-group list-group-flush mb-4">
                @foreach ($holidays as $holiday)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>
                            {{ $holiday->date->format('d M Y') }}
                            @if ($holiday->reason)
                                <span class="text-muted small">— {{ $holiday->reason }}</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('restaurant.timings.holidays.destroy', $holiday) }}" onsubmit="return confirm('Remove this holiday?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        <button type="submit" class="btn btn-brand">Save opening hours</button>
    </form>
</x-panel-form-card>
@endsection

@push('styles')
<style>
    .profile-section-nav .nav-link {
        color: var(--oniono-muted, #64748b);
        background: #f1f5f9;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .profile-section-nav .nav-link:hover {
        background: #e2e8f0;
    }
    .profile-logo-preview {
        object-fit: cover;
    }
    x-panel-form-card[id],
    [id="profile-details"],
    [id="availability"],
    [id="opening-hours"] {
        scroll-margin-top: 5rem;
    }
</style>
@endpush
