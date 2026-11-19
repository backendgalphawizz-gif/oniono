@php
    $documentService = app(\App\Services\RestaurantDocumentService::class);
    $restaurant->loadMissing('documents');
    $checklist = $documentService->checklist($restaurant);
    $allVerified = $documentService->hasAllRequiredVerified($restaurant);
    $allUploaded = $documentService->hasAllRequiredUploaded($restaurant);
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
    <div class="card-body">
        <div class="row g-3">
            @foreach ($checklist as $row)
                @php
                    $doc = $row['document'] ?? null;
                    $status = $row['status'] ?? null;
                    $url = $doc ? asset('storage/'.$doc->path) : null;
                    $isImage = $doc && $documentService->isImagePath($doc->path);
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded h-100 p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="badge text-bg-secondary mb-1">{{ $row['short_label'] }}</span>
                                <div class="fw-semibold small">{{ $row['label'] }}</div>
                            </div>
                            @if (! $doc)
                                <span class="status-badge status-cancelled">Missing</span>
                            @elseif ($status === 'verified')
                                <span class="status-badge status-delivered">Verified</span>
                            @elseif ($status === 'rejected')
                                <span class="status-badge status-cancelled">Rejected</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </div>

                        @if ($doc && $isImage)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="d-block mb-2">
                                <img src="{{ $url }}" alt="{{ $row['label'] }}" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover; width: 100%;">
                            </a>
                        @elseif ($doc)
                            <div class="text-center py-3 mb-2 rounded border bg-white">
                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                <div class="small text-muted">PDF document</div>
                            </div>
                        @else
                            <div class="text-center py-3 mb-2 rounded border border-dashed text-muted small">
                                Not uploaded
                            </div>
                        @endif

                        <p class="small text-muted mb-2" style="font-size: 0.75rem">{{ $row['description'] }}</p>

                        @if ($doc)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary w-100 mb-1">Open document</a>
                            @if (! empty($adminReview))
                                <div class="d-flex gap-1">
                                    @if ($doc->verification_status !== 'verified')
                                        <form method="POST" action="{{ route('admin.restaurants.documents.verify', $doc) }}" class="flex-grow-1">@csrf<button class="btn btn-sm btn-success w-100">Verify</button></form>
                                    @endif
                                    @if ($doc->verification_status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.restaurants.documents.reject', $doc) }}" class="flex-grow-1">@csrf<button class="btn btn-sm btn-outline-danger w-100">Reject</button></form>
                                    @endif
                                </div>
                            @endif
                            @if ($doc->admin_note)
                                <div class="small text-danger mt-2">{{ $doc->admin_note }}</div>
                            @endif
                        @elseif (! empty($showEditHint))
                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="btn btn-sm btn-brand w-100">Upload on edit</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
