@extends('layouts.admin')@section('page-title','Delivery Assignment')@section('content')
<x-panel-table title="Orders Ready for Pickup" :paginator="$orders">
<thead><tr><th>Order</th><th>Restaurant</th><th>Current Rider</th><th>Assign</th></tr></thead>
<tbody>@foreach($orders as $order)<tr>
<td>#{{ $order->order_number }}</td><td>{{ $order->restaurant->name }}</td>
<td>{{ $order->deliveryAssignment?->deliveryPartner?->name ?? '-' }}</td>
<td><form method="POST" action="{{ route('admin.delivery-assignment.assign',$order) }}" class="d-flex gap-2">@csrf
<select name="delivery_partner_id" class="form-select form-select-sm" required>@foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
<button class="btn btn-sm btn-success">{{ $order->deliveryAssignment?->deliveryPartner ? 'Reassign' : 'Assign' }}</button></form></td></tr>@endforeach</tbody></x-panel-table>
<p class="small text-muted mt-2">You can also assign or reassign from each order’s detail page when status is Ready for Pickup.</p>
@endsection
