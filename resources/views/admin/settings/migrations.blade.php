@extends('layouts.admin')
@section('page-title', 'Database migrations')
@section('content')
    @include('admin.settings.partials.migrations')

    @if (session('migration_output'))
        <div class="card card-soft mt-3">
            <div class="card-body">
                <h3 class="h6">Migration output</h3>
                <pre class="small mb-0 bg-light border rounded p-3 migration-output">{{ session('migration_output') }}</pre>
            </div>
        </div>
    @endif
@endsection
