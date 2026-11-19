<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminMigrationService;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function __construct(private AdminMigrationService $migrations) {}

    public function index()
    {
        return view('admin.settings.migrations', [
            'migrationSummary' => $this->migrations->summary(),
            'migrations' => $this->migrations->allMigrations(),
        ]);
    }

    public function runAll(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'in:1'],
        ]);

        $result = $this->migrations->runCommon();

        return redirect()
            ->route('admin.settings.migrations')
            ->with('success', $result['message'])
            ->with('migration_output', $result['output']);
    }

    public function runGroup(Request $request, string $group)
    {
        $request->validate([
            'confirm' => ['required', 'in:1'],
        ]);

        $result = $this->migrations->runGroup($group);

        $flash = $result['success'] ? 'success' : 'error';

        return redirect()
            ->route('admin.settings.migrations')
            ->with($flash, $result['message'])
            ->with('migration_output', $result['output']);
    }
}
