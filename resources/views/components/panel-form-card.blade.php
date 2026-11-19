@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'card card-soft mb-4']) }}>
    @if ($title)
        <div class="card-header-brand">
            <h2 class="h6 mb-0">{{ $title }}</h2>
            @if ($subtitle)
                <p class="small text-muted mb-0 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
