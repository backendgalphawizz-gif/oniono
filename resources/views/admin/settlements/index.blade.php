@extends('layouts.admin')
@section('page-title', 'Settlements')
@section('content')
<x-panel-table title="Settlement history" :paginator="$settlements">
    <thead>
        <tr>
            <th>Number</th>
            <th>Restaurant</th>
            <th>Amount</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($settlements as $s)
            <tr>
                <td class="fw-semibold">{{ $s->settlement_number }}</td>
                <td>{{ $s->restaurant->name }}</td>
                <td>{{ money_inr($s->amount) }}</td>
                <td><span class="status-badge status-default">{{ $s->status->value ?? $s->status }}</span></td>
                <td class="text-end">
                    <a href="{{ route('admin.settlements.show', $s) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No settlements yet.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>

<div class="card card-soft mt-3">
    <div class="card-header-brand">
        <h2 class="h6 mb-0">Start new settlement</h2>
        <p class="small text-muted mb-0 mt-1">Choose a restaurant to settle pending earnings.</p>
    </div>
    <div class="card-body d-flex flex-wrap gap-2">
        @forelse ($restaurants as $r)
            <a class="btn btn-outline-brand btn-sm" href="{{ route('admin.settlements.create', $r) }}">{{ $r->name }}</a>
        @empty
            <span class="text-muted small">No approved restaurants available.</span>
        @endforelse
    </div>
</div>
@endsection
