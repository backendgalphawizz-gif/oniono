@extends('layouts.admin')
@section('page-title', 'Delivery partner performance')
@section('content')
@include('admin.reports-analytics.partials.date-filter')

<x-panel-table title="Delivery partner performance">
    <thead>
        <tr>
            <th>Partner</th>
            <th class="text-end">Assignments</th>
            <th class="text-end">Completed deliveries</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($partners as $row)
            <tr>
                <td>
                    @if ($row->deliveryPartner)
                        <a href="{{ route('admin.delivery-partners.show', $row->delivery_partner_id) }}" class="fw-semibold">{{ $row->deliveryPartner->name }}</a>
                        <div class="small text-muted">{{ $row->deliveryPartner->phone }}</div>
                    @else
                        Partner #{{ $row->delivery_partner_id }}
                    @endif
                </td>
                <td class="text-end">{{ $row->assignments }}</td>
                <td class="text-end fw-semibold">{{ $row->completed_deliveries }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">No delivery assignments in range.</td></tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
