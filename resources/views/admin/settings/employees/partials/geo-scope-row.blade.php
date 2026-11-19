@php
    $index = $index ?? 0;
    $stateVal = old("geo_scopes.{$index}.state", $row['state'] ?? '');
    $cityVal = old("geo_scopes.{$index}.city", $row['city'] ?? '');
    $citiesByState = $citiesByState ?? [];
    $cityOptions = ($stateVal !== '' && isset($citiesByState[$stateVal]))
        ? $citiesByState[$stateVal]
        : [];
@endphp

<tr data-geo-scope-row="{{ $index }}">
    <td>
        <select
            name="geo_scopes[{{ $index }}][state]"
            id="geo_state_{{ $index }}"
            class="form-select form-select-sm"
            data-geo-state-select
            aria-label="State"
        >
            <option value="">Select state…</option>
            @foreach ($stateOptions as $stateOption)
                <option value="{{ $stateOption }}" @selected($stateVal === $stateOption)>{{ $stateOption }}</option>
            @endforeach
            @if ($stateVal && ! in_array($stateVal, $stateOptions, true))
                <option value="{{ $stateVal }}" selected>{{ $stateVal }}</option>
            @endif
        </select>
    </td>
    <td>
        <select
            name="geo_scopes[{{ $index }}][city]"
            id="geo_city_{{ $index }}"
            class="form-select form-select-sm"
            data-geo-city-select
            aria-label="City (optional)"
            @disabled($stateVal === '')
        >
            @if ($stateVal === '')
                <option value="">Select state first</option>
            @else
                <option value="">All cities in state</option>
                @foreach ($cityOptions as $cityOption)
                    <option value="{{ $cityOption }}" @selected($cityVal === $cityOption)>{{ $cityOption }}</option>
                @endforeach
                @if ($cityVal && ! in_array($cityVal, $cityOptions, true))
                    <option value="{{ $cityVal }}" selected>{{ $cityVal }}</option>
                @endif
            @endif
        </select>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger" data-geo-scope-remove aria-label="Remove location">
            <i class="bi bi-trash" aria-hidden="true"></i>
        </button>
    </td>
</tr>
