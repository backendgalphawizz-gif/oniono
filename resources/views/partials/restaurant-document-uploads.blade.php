@php
    $documentService = app(\App\Services\RestaurantDocumentService::class);
    $definitions = $documentService->documentDefinitions();
    $requireAll = $requireAll ?? false;
    if (isset($restaurant) && $restaurant) {
        $restaurant->loadMissing('documents');
    }
@endphp

<div class="col-12">
    <hr class="my-2">
    <h2 class="h6 mb-1">Business documents</h2>
    <p class="small text-muted mb-3">PAN is required; other documents are optional. Upload clear PDF or image files. Replace a file to reset verification to pending.</p>
</div>

@foreach ($definitions as $type => $meta)
    @php
        $doc = isset($restaurant) && $restaurant ? $documentService->documentForType($restaurant, $type) : null;
        $fieldRequired = $requireAll
            ? ($meta['required_for_registration'] ?? false)
            : false;
    @endphp
    <div class="col-md-6">
        <label class="form-label" for="document-{{ $type }}">
            @if (! empty($meta['short_label']))
                <span class="badge text-bg-light border me-1">{{ $meta['short_label'] }}</span>
            @endif
            {{ $meta['label'] }}
            @if ($fieldRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
        <input
            type="file"
            name="documents[{{ $type }}]"
            id="document-{{ $type }}"
            class="form-control"
            accept=".pdf,.jpg,.jpeg,.png"
            @if ($fieldRequired) required @endif
        >
        <div class="form-text">{{ $meta['description'] }} (max {{ number_format($meta['max_kb'] / 1024, 1) }} MB)</div>
        @if ($doc)
            <div class="small mt-2 p-2 rounded border bg-light">
                @if ($documentService->isImagePath($doc->path))
                    <a href="{{ asset('storage/'.$doc->path) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/'.$doc->path) }}" alt="" class="img-fluid rounded mb-1" style="max-height: 100px;">
                    </a>
                @endif
                <div>
                    Current file:
                    <a href="{{ asset('storage/'.$doc->path) }}" target="_blank" rel="noopener">View / download</a>
                    · {{ ucfirst($doc->verification_status) }}
                </div>
            </div>
        @endif
    </div>
@endforeach
