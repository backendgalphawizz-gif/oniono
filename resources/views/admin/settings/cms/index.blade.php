@extends('layouts.admin')
@section('page-title', 'CMS & Static Pages')
@section('content')

<x-panel-form-card title="CMS & Static Pages" subtitle="Manage legal, support, and informational content shown in apps and on the website.">
    <p class="small text-muted mb-0">
        Content supports HTML (headings, links, lists). Each page has a fixed URL slug for API and web use.
    </p>
</x-panel-form-card>

<x-panel-table title="Static pages">
    <thead>
        <tr>
            <th>Page</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Last updated</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pages as $page)
            <tr>
                <td class="fw-semibold">{{ $page->title }}</td>
                <td><code class="small">{{ $page->slug }}</code></td>
                <td>
                    @if ($page->is_active)
                        <span class="status-badge status-delivered">Published</span>
                    @else
                        <span class="status-badge status-default">Hidden</span>
                    @endif
                </td>
                <td class="text-muted small">{{ $page->updated_at?->format('M j, Y g:i A') ?? '—' }}</td>
                <td class="text-end">
                    @admincan('settings_cms', 'edit')
                        <a href="{{ route('admin.settings.cms.edit', $page->slug) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    @endadmincan
                </td>
            </tr>
        @endforeach
    </tbody>
</x-panel-table>

@endsection
