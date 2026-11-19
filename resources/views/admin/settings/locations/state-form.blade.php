@extends('layouts.admin')
@section('page-title', $state ? 'Edit state' : 'Add state')
@section('content')

@php
    $state = $state ?? null;
    $method = $method ?? null;
@endphp

<x-panel-form-card :title="$state ? 'Edit state' : 'New state'" subtitle="Used across registration and region scoping">
    <form method="POST" action="{{ $action }}" class="panel-form">
        @csrf
        @if ($method)
            @method($method)
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="field-state-name">State name</label>
                <input name="name" id="field-state-name" class="form-control" value="{{ old('name', $state?->name) }}" required maxlength="100">
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-brand">Save state</button>
            <a href="{{ route('admin.settings.locations.index', $state ? ['state_id' => $state->id] : []) }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</x-panel-form-card>

@endsection
