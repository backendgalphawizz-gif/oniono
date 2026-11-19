@php
    $documentService = app(\App\Services\DeliveryPartnerDocumentService::class);
    $definitions = $documentService->requiredDefinitions();
    $requireAll = $requireAll ?? false;
    if (isset($partner) && $partner) {
        $partner->loadMissing('documents');
    }
@endphp

<div class="col-12">
    <hr class="my-2">
    <h2 class="h6 mb-1">Required documents</h2>
    <p class="small text-muted mb-3">Upload clear PDF or image files. All documents are required before the partner can be approved for deliveries.</p>
</div>

@foreach ($definitions as $type => $meta)
    @php $doc = isset($partner) && $partner ? $documentService->documentForType($partner, $type) : null; @endphp
    <div class="col-md-6">
        <label class="form-label" for="dp-document-{{ $type }}">
            @if (! empty($meta['short_label']))
                <span class="badge text-bg-light border me-1">{{ $meta['short_label'] }}</span>
            @endif
            {{ $meta['label'] }}
            @if ($requireAll)
                <span class="text-danger">*</span>
            @endif
        </label>
        <input
            type="file"
            name="documents[{{ $type }}]"
            id="dp-document-{{ $type }}"
            class="form-control"
            accept=".pdf,.jpg,.jpeg,.png"
            @if ($requireAll) required @endif
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
