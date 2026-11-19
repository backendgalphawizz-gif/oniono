@extends('layouts.admin')
@section('page-title', 'Edit Delivery Partner')
@section('content')
@include('partials.delivery-partner-documents-display', [
    'deliveryPartner' => $deliveryPartner,
    'adminReview' => false,
])
@include('admin.delivery-partners._form', [
    'action' => route('admin.delivery-partners.update', $deliveryPartner),
    'method' => 'PUT',
    'partner' => $deliveryPartner,
    'masterCommission' => $masterCommission,
])
@endsection
