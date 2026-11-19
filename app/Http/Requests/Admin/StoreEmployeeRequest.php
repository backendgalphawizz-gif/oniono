<?php

namespace App\Http\Requests\Admin;

use App\Services\AdminPermissionSyncService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login_id' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('admins', 'login_id')],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.view' => ['nullable', 'boolean'],
            'permissions.*.edit' => ['nullable', 'boolean'],
            'permissions.*.delete' => ['nullable', 'boolean'],
            'region_access' => ['required', Rule::in(['all', 'limited'])],
            'geo_scopes' => ['nullable', 'array'],
            'geo_scopes.*.state' => ['required_with:geo_scopes.*.city', 'string', 'max:100'],
            'geo_scopes.*.city' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('region_access') === 'limited' && ! $this->hasGeoScopeRows()) {
                $validator->errors()->add('geo_scopes', 'Add at least one state (and optional city) for limited regional access.');
            }

            if (! $this->boolean('is_active')) {
                return;
            }

            if (! $this->hasAnyViewPermission()) {
                $validator->errors()->add('permissions', 'Select at least one module with View access for an active employee.');
            }
        });
    }

    protected function hasGeoScopeRows(): bool
    {
        $rows = $this->input('geo_scopes', []);
        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (trim((string) ($row['state'] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    protected function hasAnyViewPermission(): bool
    {
        $permissions = $this->input('permissions', []);
        if (! is_array($permissions)) {
            return false;
        }

        foreach (app(AdminPermissionSyncService::class)->allModuleKeys() as $module) {
            $row = $permissions[$module] ?? [];
            if (! empty($row['view']) || ! empty($row['edit']) || ! empty($row['delete'])) {
                return true;
            }
        }

        return false;
    }
}
