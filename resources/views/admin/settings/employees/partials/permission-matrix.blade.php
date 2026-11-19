@php
    $matrix = $permissionMatrix ?? [];
@endphp

<div class="permission-matrix-wrap">
    <div class="permission-matrix-head">
        <div class="permission-matrix-head-copy">
            <p class="form-section-title mb-2">Module permissions</p>
            <p class="small text-muted mb-0">Create actions use Edit permission. Edit or Delete automatically enables View for that module.</p>
        </div>
        <div class="permission-matrix-bulk btn-toolbar flex-wrap gap-2" role="toolbar" aria-label="Bulk permission actions">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" data-perm-col="view">
                    <i class="bi bi-eye me-1" aria-hidden="true"></i>All view
                </button>
                <button type="button" class="btn btn-outline-secondary" data-perm-col="edit">
                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>All edit
                </button>
                <button type="button" class="btn btn-outline-secondary" data-perm-col="delete">
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>All delete
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" data-perm-clear>
                <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Clear all
            </button>
        </div>
    </div>

    <div class="permission-matrix-scroll table-responsive">
        <table class="table table-sm align-middle permission-matrix mb-0">
            <thead>
                <tr>
                    <th scope="col" class="permission-matrix-col-module">Module</th>
                    <th scope="col" class="permission-matrix-col-check text-center">View</th>
                    <th scope="col" class="permission-matrix-col-check text-center">Edit</th>
                    <th scope="col" class="permission-matrix-col-check text-center">Delete</th>
                    <th scope="col" class="permission-matrix-col-row text-center">Quick</th>
                </tr>
            </thead>
            <tbody>
                @foreach (admin_permission_groups() as $group)
                    <tr class="permission-matrix-group">
                        <td colspan="5">{{ $group['label'] }}</td>
                    </tr>
                    @foreach ($group['modules'] as $moduleKey => $moduleLabel)
                        @php
                            $row = $matrix[$moduleKey] ?? ['view' => false, 'edit' => false, 'delete' => false];
                        @endphp
                        <tr data-perm-row="{{ $moduleKey }}">
                            <td class="permission-matrix-module">{{ $moduleLabel }}</td>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input perm-view"
                                    name="permissions[{{ $moduleKey }}][view]"
                                    value="1"
                                    aria-label="View {{ $moduleLabel }}"
                                    @checked(old("permissions.{$moduleKey}.view", $row['view']))
                                >
                            </td>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input perm-edit"
                                    name="permissions[{{ $moduleKey }}][edit]"
                                    value="1"
                                    aria-label="Edit {{ $moduleLabel }}"
                                    @checked(old("permissions.{$moduleKey}.edit", $row['edit']))
                                >
                            </td>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input perm-delete"
                                    name="permissions[{{ $moduleKey }}][delete]"
                                    value="1"
                                    aria-label="Delete {{ $moduleLabel }}"
                                    @checked(old("permissions.{$moduleKey}.delete", $row['delete']))
                                >
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm permission-matrix-row-actions" role="group" aria-label="Row shortcuts for {{ $moduleLabel }}">
                                    <button type="button" class="btn btn-outline-secondary" data-perm-row-all="{{ $moduleKey }}">All</button>
                                    <button type="button" class="btn btn-outline-secondary" data-perm-row-none="{{ $moduleKey }}">None</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function rowEl(moduleKey) {
                    return document.querySelector('[data-perm-row="' + moduleKey + '"]');
                }

                function syncViewFromEditDelete(tr) {
                    if (!tr) return;
                    var view = tr.querySelector('.perm-view');
                    var edit = tr.querySelector('.perm-edit');
                    var del = tr.querySelector('.perm-delete');
                    if ((edit && edit.checked) || (del && del.checked)) {
                        if (view) view.checked = true;
                    }
                }

                document.querySelectorAll('.perm-edit, .perm-delete').forEach(function (el) {
                    el.addEventListener('change', function () {
                        syncViewFromEditDelete(el.closest('tr'));
                    });
                });

                document.querySelectorAll('[data-perm-row-all]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var tr = rowEl(btn.getAttribute('data-perm-row-all'));
                        if (!tr) return;
                        tr.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = true; });
                    });
                });

                document.querySelectorAll('[data-perm-row-none]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var tr = rowEl(btn.getAttribute('data-perm-row-none'));
                        if (!tr) return;
                        tr.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = false; });
                    });
                });

                document.querySelectorAll('[data-perm-col]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var col = btn.getAttribute('data-perm-col');
                        document.querySelectorAll('.perm-' + col).forEach(function (cb) {
                            cb.checked = true;
                            syncViewFromEditDelete(cb.closest('tr'));
                        });
                    });
                });

                var clearBtn = document.querySelector('[data-perm-clear]');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        document.querySelectorAll('.permission-matrix input[type=checkbox]').forEach(function (cb) {
                            cb.checked = false;
                        });
                    });
                }
            })();
        </script>
    @endpush
@endonce