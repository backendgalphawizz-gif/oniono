@php
    $helpHomeActive = request()->routeIs('restaurant.help.index');
    $supportActive = request()->routeIs('restaurant.support.*');
@endphp
<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Support and help">
    <a href="{{ route('restaurant.help.index') }}" class="btn btn-sm {{ $helpHomeActive ? 'btn-brand' : 'btn-outline-secondary' }}">
        <i class="bi bi-life-preserver me-1"></i> Help home
    </a>
    <a href="{{ route('restaurant.support.index') }}" class="btn btn-sm {{ $supportActive ? 'btn-brand' : 'btn-outline-secondary' }}">
        <i class="bi bi-ticket-detailed me-1"></i> My tickets
    </a>
    <a href="{{ route('restaurant.help.contact') }}" class="btn btn-sm {{ request()->routeIs('restaurant.help.contact') ? 'btn-brand' : 'btn-outline-secondary' }}">
        <i class="bi bi-headset me-1"></i> Contact support
    </a>
    <a href="{{ route('restaurant.help.page', 'faqs') }}" class="btn btn-sm {{ request()->routeIs('restaurant.help.page') && request()->route('slug') === 'faqs' ? 'btn-brand' : 'btn-outline-secondary' }}">
        <i class="bi bi-patch-question me-1"></i> FAQs
    </a>
</nav>
