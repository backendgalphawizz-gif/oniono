@extends('layouts.admin')
@section('page-title', $city ? 'Edit city' : 'Add city')
@section('content')

@php
    $city = $city ?? null;
    $method = $method ?? null;
    $selectedStateId = old('state_id', $city?->state_id ?? $state?->id);
@endphp

<x-panel-form-card :title="$city ? 'Edit city' : 'New city'" subtitle="Cities belong to a single state">
    <form method="POST" action="{{ $action }}" class="panel-form">
        @csrf
        @if ($method)
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field-city-state">State</label>
                <select name="state_id" id="field-city-state" class="form-select" required>
                    <option value="">Select state…</option>
                    @foreach ($states as $stateOption)
                        <option value="{{ $stateOption->id }}" @selected((string) $selectedStateId === (string) $stateOption->id)>{{ $stateOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="field-city-name">City name</label>
                <input name="name" id="field-city-name" class="form-control" value="{{ old('name', $city?->name) }}" required maxlength="100">
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">Save city</button>
            <a href="{{ route('admin.settings.locations.index', $selectedStateId ? ['state_id' => $selectedStateId] : []) }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>

@endsection
