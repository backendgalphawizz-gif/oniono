@php
    $checklist = $documentChecklist ?? app(\App\Services\RestaurantDocumentService::class)->checklist($restaurant);
    $allUploaded = app(\App\Services\RestaurantDocumentService::class)->hasAllRequiredUploaded($restaurant);
    $allVerified = app(\App\Services\RestaurantDocumentService::class)->hasAllRequiredVerified($restaurant);
@endphp

<div class="card card-soft mb-3">
    <div class="card-header-brand d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="h6 mb-0">Required documents</h3>
        @if ($allVerified)
            <span class="badge text-bg-success">All verified</span>
        @elseif ($allUploaded)
            <span class="badge text-bg-warning">Pending verification</span>
        @else
            <span class="badge text-bg-danger">Incomplete</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th class="text-end">{{ ! empty($adminReview) ? 'Actions' : 'File' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checklist as $row)
                        <tr>
                            <td>
                                <div class="fw-semibold small">
                                    @if (! empty($row['short_label']) && $row['short_label'] !== $row['label'])
                                        <span class="badge text-bg-light border me-1">{{ $row['short_label'] }}</span>
                                    @endif
                                    {{ $row['label'] }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem">{{ $row['description'] }}</div>
                                @if (! empty($adminReview) && $row['document']?->admin_note)
                                    <div class="small text-danger mt-1">{{ $row['document']->admin_note }}</div>
                                @endif
                            </td>
                            <td>
                                @if (! $row['uploaded'])
                                    <span class="status-badge status-cancelled">Missing</span>
                                @elseif ($row['status'] === 'verified')
                                    <span class="status-badge status-delivered">Verified</span>
                                @elseif ($row['status'] === 'rejected')
                                    <span class="status-badge status-cancelled">Rejected</span>
                                @else
                                    <span class="status-badge status-pending">Pending review</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($row['document'])
                                    <a href="{{ asset('storage/'.$row['document']->path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View</a>
                                    @if (! empty($adminReview))
                                        @if ($row['document']->verification_status !== 'verified')
                                            <form method="POST" action="{{ route('admin.restaurants.documents.verify', $row['document']) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Verify</button></form>
                                        @endif
                                        @if ($row['document']->verification_status !== 'rejected')
                                            <form method="POST" action="{{ route('admin.restaurants.documents.reject', $row['document']) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                                        @endif
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
