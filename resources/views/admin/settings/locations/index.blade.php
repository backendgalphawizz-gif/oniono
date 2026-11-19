@extends('layouts.admin')
@section('page-title', 'States & cities')
@section('content')

<x-panel-form-card title="States & cities" subtitle="Master list used in restaurant registration, admin region scopes, and location filters.">
    <p class="small text-muted mb-0">
        Renaming a state or city updates matching restaurant addresses and staff region rules automatically.
    </p>
</x-panel-form-card>

@if ($errors->has('state') || $errors->has('city'))
    <div class="alert alert-danger">{{ $errors->first('state') ?: $errors->first('city') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <x-panel-toolbar>
            <x-slot:filter>
                <div class="col-12 filter-field">
                    <label class="form-label" for="filter-state-search">Search states</label>
                    <input type="search" name="state_search" id="filter-state-search" class="form-control" placeholder="State name…" value="{{ request('state_search') }}">
                </div>
            </x-slot:filter>
            <x-slot:actions>
                @admincan('settings_locations', 'edit')
                    <a href="{{ route('admin.settings.locations.states.create') }}" class="btn btn-brand">
                        <i class="bi bi-plus-lg me-1"></i>Add state
                    </a>
                @endadmincan
            </x-slot:actions>
        </x-panel-toolbar>

        <x-panel-table title="States" :paginator="$states">
            <thead>
                <tr>
                    <th>State</th>
                    <th>Cities</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($states as $stateRow)
                    @php $isSelected = $selectedState && $selectedState->id === $stateRow->id; @endphp
                    <tr class="{{ $isSelected ? 'table-active' : '' }}">
                        <td class="fw-semibold">
                            <a href="{{ route('admin.settings.locations.index', array_merge(request()->except('states_page'), ['state_id' => $stateRow->id])) }}" class="text-decoration-none {{ $isSelected ? 'text-dark' : '' }}">
                                {{ $stateRow->name }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $stateRow->cities_count }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @admincan('settings_locations', 'edit')
                                    <a href="{{ route('admin.settings.locations.states.edit', $stateRow) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endadmincan
                                @admincan('settings_locations', 'delete')
                                    <form method="POST" action="{{ route('admin.settings.locations.states.destroy', $stateRow) }}" class="d-inline" onsubmit="return confirm('Delete this state and all its cities?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endadmincan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No states yet. Add one or run the State/City seeder.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-panel-table>
    </div>

    <div class="col-lg-7">
        @if ($selectedState)
            <x-panel-toolbar :action="route('admin.settings.locations.index', array_merge(request()->only('state_search'), ['state_id' => $selectedState->id]))">
                <x-slot:filter>
                    <div class="col-md-6 filter-field">
                        <label class="form-label" for="filter-city-search">Search cities</label>
                        <input type="hidden" name="state_id" value="{{ $selectedState->id }}">
                        <input type="search" name="city_search" id="filter-city-search" class="form-control" placeholder="City name…" value="{{ request('city_search') }}">
                    </div>
                </x-slot:filter>
                <x-slot:actions>
                    @admincan('settings_locations', 'edit')
                        <a href="{{ route('admin.settings.locations.cities.create', ['state_id' => $selectedState->id]) }}" class="btn btn-brand">
                            <i class="bi bi-plus-lg me-1"></i>Add city
                        </a>
                    @endadmincan
                </x-slot:actions>
            </x-panel-toolbar>

            <x-panel-table :title="'Cities in '.$selectedState->name" :paginator="$cities">
                <thead>
                    <tr>
                        <th>City</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $cityRow)
                        <tr>
                            <td class="fw-semibold">{{ $cityRow->name }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @admincan('settings_locations', 'edit')
                                        <a href="{{ route('admin.settings.locations.cities.edit', $cityRow) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    @endadmincan
                                    @admincan('settings_locations', 'delete')
                                        <form method="POST" action="{{ route('admin.settings.locations.cities.destroy', $cityRow) }}" class="d-inline" onsubmit="return confirm('Delete this city?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endadmincan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">No cities in this state yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-panel-table>
        @else
            <x-panel-form-card title="Cities" subtitle="Select a state">
                <p class="text-muted mb-0">Add a state on the left to manage cities.</p>
            </x-panel-form-card>
        @endif
    </div>
</div>

@endsection
