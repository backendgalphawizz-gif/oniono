@extends('layouts.admin')
@section('page-title', 'Food Category & Menu')
@section('content')
<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'Main categories', 'value' => $stats['categories'], 'route' => 'admin.categories.index'],
        ['label' => 'Sub-categories', 'value' => $stats['sub_categories'], 'route' => 'admin.sub-categories.index'],
        ['label' => 'Cuisines', 'value' => $stats['cuisines'], 'route' => 'admin.cuisines.index'],
        ['label' => 'Food items', 'value' => $stats['food_items'], 'route' => 'admin.food-items.index'],
        ['label' => 'Featured restaurants', 'value' => $stats['featured_restaurants'], 'route' => 'admin.menu-management.featured'],
        ['label' => 'Featured dishes', 'value' => $stats['featured_food_items'], 'route' => 'admin.menu-management.featured'],
    ] as $card)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route($card['route']) }}" class="text-decoration-none">
                <div class="card card-soft h-100">
                    <div class="card-body">
                        <div class="small text-muted">{{ $card['label'] }}</div>
                        <div class="h4 mb-0 text-dark">{{ $card['value'] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="card card-soft">
    <div class="card-body">
        <h2 class="h6 mb-3">Quick actions</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-brand btn-sm">Add category</a>
            <a href="{{ route('admin.sub-categories.create') }}" class="btn btn-outline-brand btn-sm">Add sub-category</a>
            <a href="{{ route('admin.cuisines.create') }}" class="btn btn-outline-brand btn-sm">Add cuisine</a>
            <a href="{{ route('admin.food-items.create') }}" class="btn btn-outline-brand btn-sm">Add food item</a>
            <a href="{{ route('admin.menu-management.featured') }}" class="btn btn-outline-secondary btn-sm">Manage featured</a>
        </div>
        <p class="small text-muted mt-3 mb-0">
            Admin food price editing is {{ \App\Models\Setting::getValue('admin_food_price_editing', true) ? 'enabled' : 'disabled' }}.
            Change this under <a href="{{ route('admin.settings.general') }}">General settings</a>.
        </p>
    </div>
</div>
@endsection
