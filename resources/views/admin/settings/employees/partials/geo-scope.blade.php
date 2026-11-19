@php
    $regionAccess = old('region_access', ($admin->scope_all_regions ?? true) ? 'all' : 'limited');
    $scopeRows = old('geo_scopes');
    if (! is_array($scopeRows)) {
        $scopeRows = isset($geoScopeRows) && is_array($geoScopeRows) ? $geoScopeRows : [['state' => '', 'city' => '']];
    }
    if ($scopeRows === []) {
        $scopeRows = [['state' => '', 'city' => '']];
    }
    $stateOptions = $stateOptions ?? [];
    $citiesByState = $citiesByState ?? [];
@endphp

<div class="geo-scope-wrap">
    <p class="form-section-title">Regional access</p>
    <p class="small text-muted mb-3">
        Limit this manager to restaurants (and linked orders, menu, settlements) in selected states or cities.
        Leave on “All regions” unless you need territory-based managers.
    </p>

    <div class="btn-group btn-group-sm mb-3" role="group" aria-label="Regional access mode">
        <input type="radio" class="btn-check" name="region_access" id="region_access_all" value="all" @checked($regionAccess === 'all')>
        <label class="btn btn-outline-secondary" for="region_access_all">All regions</label>

        <input type="radio" class="btn-check" name="region_access" id="region_access_limited" value="limited" @checked($regionAccess === 'limited')>
        <label class="btn btn-outline-secondary" for="region_access_limited">Selected states / cities</label>
    </div>

    <div class="@if ($regionAccess !== 'limited') d-none @endif" data-geo-scope-panel>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <span class="small fw-semibold text-secondary">Allowed locations</span>
            <button type="button" class="btn btn-sm btn-outline-primary" data-geo-scope-add>
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add row
            </button>
        </div>

        <div class="geo-scope-scroll table-responsive">
            <table class="table table-sm align-middle geo-scope-table mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 200px">State</th>
                        <th scope="col" style="min-width: 180px">City <span class="text-muted fw-normal">(optional)</span></th>
                        <th scope="col" class="text-center" style="width: 4.5rem">Remove</th>
                    </tr>
                </thead>
                <tbody data-geo-scope-list>
                    @foreach ($scopeRows as $index => $row)
                        @include('admin.settings.employees.partials.geo-scope-row', [
                            'index' => $index,
                            'row' => $row,
                            'stateOptions' => $stateOptions,
                            'citiesByState' => $citiesByState,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        @error('geo_scopes')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror

        <p class="form-text mb-0 mt-2">
            Leave city blank to allow the <strong>entire state</strong>. City filter matches the restaurant’s city field.
        </p>
    </div>
</div>

<template id="geo-scope-row-template">
    <table><tbody>
        @include('admin.settings.employees.partials.geo-scope-row', [
            'index' => '__INDEX__',
            'row' => ['state' => '', 'city' => ''],
            'stateOptions' => $stateOptions,
            'citiesByState' => $citiesByState,
        ])
    </tbody></table>
</template>

@once
    @push('scripts')
        <script>
            (function () {
                var citiesByState = @json($citiesByState);

                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');
                }

                function syncCitySelectForRow(row, preserveCity) {
                    var stateSelect = row?.querySelector('[data-geo-state-select]');
                    var citySelect = row?.querySelector('[data-geo-city-select]');
                    if (!stateSelect || !citySelect) {
                        return;
                    }

                    var state = stateSelect.value;
                    var previousCity = preserveCity ? citySelect.value : '';

                    if (!state) {
                        citySelect.innerHTML = '<option value="">Select state first</option>';
                        citySelect.value = '';
                        citySelect.disabled = true;
                        return;
                    }

                    var cities = citiesByState[state] || [];
                    var options = '<option value="">All cities in state</option>';
                    cities.forEach(function (city) {
                        var selected = preserveCity && city === previousCity ? ' selected' : '';
                        options += '<option value="' + escapeHtml(city) + '"' + selected + '>' + escapeHtml(city) + '</option>';
                    });

                    if (preserveCity && previousCity && cities.indexOf(previousCity) === -1) {
                        options += '<option value="' + escapeHtml(previousCity) + '" selected>' + escapeHtml(previousCity) + '</option>';
                    }

                    citySelect.innerHTML = options;
                    citySelect.disabled = false;

                    if (!preserveCity) {
                        citySelect.value = '';
                    }
                }

                function togglePanel() {
                    var limited = document.getElementById('region_access_limited')?.checked;
                    var panel = document.querySelector('[data-geo-scope-panel]');
                    if (panel) {
                        panel.classList.toggle('d-none', !limited);
                    }
                }

                document.getElementById('region_access_all')?.addEventListener('change', togglePanel);
                document.getElementById('region_access_limited')?.addEventListener('change', togglePanel);

                function nextIndex() {
                    var list = document.querySelector('[data-geo-scope-list]');
                    if (!list) return 0;
                    var max = -1;
                    list.querySelectorAll('[data-geo-scope-row]').forEach(function (row) {
                        var idx = parseInt(row.getAttribute('data-geo-scope-row'), 10);
                        if (!isNaN(idx) && idx > max) max = idx;
                    });
                    return max + 1;
                }

                document.querySelector('[data-geo-scope-add]')?.addEventListener('click', function () {
                    var tpl = document.getElementById('geo-scope-row-template');
                    var list = document.querySelector('[data-geo-scope-list]');
                    if (!tpl || !list) return;
                    var idx = nextIndex();
                    var html = tpl.innerHTML.replace(/__INDEX__/g, String(idx));
                    var wrap = document.createElement('tbody');
                    wrap.innerHTML = html.trim();
                    var row = wrap.querySelector('tr[data-geo-scope-row]');
                    if (row) {
                        list.appendChild(row);
                        syncCitySelectForRow(row, false);
                    }
                });

                document.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-geo-scope-remove]');
                    if (!btn) return;
                    var row = btn.closest('[data-geo-scope-row]');
                    var list = document.querySelector('[data-geo-scope-list]');
                    if (!row || !list) return;
                    if (list.querySelectorAll('[data-geo-scope-row]').length <= 1) {
                        row.querySelectorAll('input, select').forEach(function (el) { el.value = ''; });
                        syncCitySelectForRow(row, false);
                        return;
                    }
                    row.remove();
                });

                document.addEventListener('change', function (e) {
                    var select = e.target.closest('[data-geo-state-select]');
                    if (!select) return;
                    var row = select.closest('[data-geo-scope-row]');
                    syncCitySelectForRow(row, false);
                });

                document.querySelectorAll('[data-geo-scope-row]').forEach(function (row) {
                    syncCitySelectForRow(row, true);
                });
            })();
        </script>
    @endpush
@endonce
