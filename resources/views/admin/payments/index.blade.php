@extends('layouts.admin')
@section('page-title', 'Payments')
@section('content')
<x-panel-table title="Payments" :paginator="$payments">
    <thead>
        <tr>
            <th>Order</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($payments as $payment)
            <tr>
                <td class="fw-semibold">#{{ $payment->order?->order_number ?? '—' }}</td>
                <td>{{ $payment->method }}</td>
                <td>{{ money_inr($payment->amount) }}</td>
                <td><span class="status-badge status-default">{{ $payment->status->value ?? $payment->status }}</span></td>
                <td class="text-end">
                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No payments recorded.</td>
            </tr>
        @endforelse
    </tbody>
</x-panel-table>
@endsection
