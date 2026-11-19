<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminPermission;

class AdminPermissionSyncService
{
    /**
     * @param  array<string, array<string, mixed>>  $input  permissions[module][view|edit|delete]
     */
    public function sync(Admin $admin, array $input): void
    {
        $moduleKeys = $this->allModuleKeys();

        foreach ($moduleKeys as $module) {
            $row = $input[$module] ?? [];
            $canView = ! empty($row['view']);
            $canEdit = ! empty($row['edit']);
            $canDelete = ! empty($row['delete']);

            if ($canEdit || $canDelete) {
                $canView = true;
            }

            if (! $canView && ! $canEdit && ! $canDelete) {
                AdminPermission::query()
                    ->where('admin_id', $admin->id)
                    ->where('module', $module)
                    ->delete();

                continue;
            }

            AdminPermission::query()->updateOrCreate(
                ['admin_id' => $admin->id, 'module' => $module],
                [
                    'can_view' => $canView,
                    'can_edit' => $canEdit,
                    'can_delete' => $canDelete,
                ]
            );
        }
    }

    /**
     * @return list<string>
     */
    public function allModuleKeys(): array
    {
        $keys = [];
        foreach (admin_permission_groups() as $group) {
            foreach ($group['modules'] as $key => $label) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array<string, array{view: bool, edit: bool, delete: bool}>
     */
    public function matrixForAdmin(Admin $admin): array
    {
        $admin->loadMissing('permissions');
        $matrix = [];
        foreach ($this->allModuleKeys() as $module) {
            $permission = $admin->permissions->firstWhere('module', $module);
            $matrix[$module] = [
                'view' => (bool) ($permission?->can_view),
                'edit' => (bool) ($permission?->can_edit),
                'delete' => (bool) ($permission?->can_delete),
            ];
        }

        return $matrix;
    }
}
