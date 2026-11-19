@extends('layouts.admin')
@section('page-title', 'Edit: '.$staticPage->title)
@section('content')

<x-panel-form-card title="{{ $definition['nav_label'] ?? $staticPage->title }}" subtitle="Slug: {{ $staticPage->slug }}">
    <form method="POST" action="{{ route('admin.settings.cms.update', $staticPage->slug) }}" class="panel-form">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="title">Page title</label>
                <input name="title" id="title" class="form-control" value="{{ old('title', $staticPage->title) }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $staticPage->is_active))>
                    <label class="form-check-label" for="is_active">Published (visible to users)</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label" for="content">Content</label>
                <textarea name="content" id="content" class="form-control font-monospace" rows="16" placeholder="HTML or plain text">{{ old('content', $staticPage->content) }}</textarea>
                <div class="form-text">Use HTML for formatting. Preview in your app after saving.</div>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            @admincan('settings_cms', 'edit')
                <button type="submit" class="btn btn-brand">Save page</button>
            @endadmincan
            <a href="{{ route('admin.settings.cms.index') }}" class="btn btn-light border">Back to CMS</a>
        </div>
    </form>
</x-panel-form-card>

@endsection
