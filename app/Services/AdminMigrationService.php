<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdminMigrationService
{
    /**
     * @return Collection<int, array{migration: string, file: string, status: string, group: string|null}>
     */
    public function allMigrations(): Collection
    {
        $ran = DB::table('migrations')->pluck('migration');
        $fileToGroup = $this->fileToGroupMap();

        return collect(File::glob(database_path('migrations/*.php')))
            ->map(function (string $path) use ($ran, $fileToGroup) {
                $file = basename($path);
                $migration = basename($path, '.php');

                return [
                    'migration' => $migration,
                    'file' => $file,
                    'status' => $ran->contains($migration) ? 'ran' : 'pending',
                    'group' => $fileToGroup[$file] ?? null,
                ];
            })
            ->sortBy('migration')
            ->values();
    }

    /**
     * @return array{pending: int, ran: int, groups: array<string, array{label: string, tables: string, pending: int, ran: int, total: int}>}
     */
    public function summary(): array
    {
        $all = $this->allMigrations();
        $groupsConfig = config('oniono_migrations.groups', []);
        $groups = [];

        foreach ($groupsConfig as $key => $meta) {
            $files = collect($meta['files'] ?? []);
            $inGroup = $all->whereIn('file', $files);
            $groups[$key] = [
                'label' => $meta['label'],
                'tables' => $meta['tables'] ?? '',
                'pending' => $inGroup->where('status', 'pending')->count(),
                'ran' => $inGroup->where('status', 'ran')->count(),
                'total' => $files->count(),
            ];
        }

        return [
            'pending' => $all->where('status', 'pending')->count(),
            'ran' => $all->where('status', 'ran')->count(),
            'groups' => $groups,
        ];
    }

    /**
     * @return array{success: bool, message: string, output: string}
     */
    public function runCommon(): array
    {
        if ($this->allMigrations()->where('status', 'pending')->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No pending migrations.',
                'output' => '',
            ];
        }

        Artisan::call('migrate', ['--force' => true]);

        return [
            'success' => true,
            'message' => 'All pending migrations completed.',
            'output' => trim(Artisan::output()),
        ];
    }

    /**
     * @return array{success: bool, message: string, output: string}
     */
    public function runGroup(string $groupKey): array
    {
        $groups = config('oniono_migrations.groups', []);
        if (! isset($groups[$groupKey])) {
            return [
                'success' => false,
                'message' => 'Unknown migration group.',
                'output' => '',
            ];
        }

        $files = $groups[$groupKey]['files'] ?? [];
        $pending = $this->allMigrations()
            ->where('status', 'pending')
            ->whereIn('file', $files)
            ->pluck('file');

        if ($pending->isEmpty()) {
            return [
                'success' => true,
                'message' => 'This group is already up to date.',
                'output' => '',
            ];
        }

        $output = [];
        foreach ($pending as $file) {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/'.$file,
            ]);
            $output[] = trim(Artisan::output());
        }

        return [
            'success' => true,
            'message' => 'Group migrations completed.',
            'output' => implode("\n", array_filter($output)),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function fileToGroupMap(): array
    {
        $map = [];
        foreach (config('oniono_migrations.groups', []) as $key => $meta) {
            foreach ($meta['files'] ?? [] as $file) {
                $map[$file] = $key;
            }
        }

        return $map;
    }
}
