<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-mini h-100">
            <div class="text-muted small">Pending migrations</div>
            <div class="fw-bold fs-3">{{ $migrationSummary['pending'] }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-mini h-100">
            <div class="text-muted small">Applied migrations</div>
            <div class="fw-bold fs-3">{{ $migrationSummary['ran'] }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-mini h-100 border-warning-subtle">
            <div class="text-muted small">Run mode</div>
            <div class="fw-semibold">Common or table-wise</div>
            <div class="small text-muted mt-1">Use only on trusted environments.</div>
        </div>
    </div>
</div>

<x-panel-form-card title="Common migration" subtitle="Runs every pending migration in the standard Laravel order (recommended after deploy).">
    <form method="POST" action="{{ route('admin.settings.migrations.run-all') }}" class="panel-form" onsubmit="return confirm('Run all pending database migrations?');">
        @csrf
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="confirm" id="confirm-all" value="1" required>
            <label class="form-check-label" for="confirm-all">I confirm this should run all pending migrations</label>
        </div>
        @admincan('settings_migrations', 'edit')
            <button type="submit" class="btn btn-brand" @disabled($migrationSummary['pending'] === 0)>
                <i class="bi bi-database-check me-1"></i>Run common migration
            </button>
        @endadmincan
        @if ($migrationSummary['pending'] === 0)
            <span class="small text-muted ms-2">Database is up to date.</span>
        @endif
    </form>
</x-panel-form-card>

<x-panel-form-card title="Table-wise migration" subtitle="Run pending migrations for a specific domain group only." class="mt-3">
    <div class="row g-3">
        @foreach ($migrationSummary['groups'] as $groupKey => $group)
            <div class="col-md-6 col-xl-4">
                <div class="border rounded-3 p-3 h-100 migration-group-card">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $group['label'] }}</div>
                            <div class="small text-muted">{{ $group['tables'] }}</div>
                        </div>
                        @if ($group['pending'] > 0)
                            <span class="status-badge status-pending">{{ $group['pending'] }} pending</span>
                        @else
                            <span class="status-badge status-delivered">Up to date</span>
                        @endif
                    </div>
                    <div class="small text-muted mb-3">{{ $group['ran'] }} / {{ $group['total'] }} applied</div>
                    <form method="POST" action="{{ route('admin.settings.migrations.run-group', $groupKey) }}" onsubmit="return confirm('Run pending migrations for {{ $group['label'] }}?');">
                        @csrf
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn-outline-brand btn-sm w-100" @disabled($group['pending'] === 0)>
                            Run {{ $group['label'] }} migrations
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-panel-form-card>

<div class="mt-3">
<x-panel-table title="Migration status">
    <thead>
        <tr>
            <th>Migration</th>
            <th>Group</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($migrations as $migration)
            <tr>
                <td class="small font-monospace">{{ $migration['migration'] }}</td>
                <td>{{ $migration['group'] ? ($migrationSummary['groups'][$migration['group']]['label'] ?? '—') : 'Other' }}</td>
                <td>
                    @if ($migration['status'] === 'ran')
                        <span class="status-badge status-delivered">Applied</span>
                    @else
                        <span class="status-badge status-pending">Pending</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</x-panel-table>
</div>
