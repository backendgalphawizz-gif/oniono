<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'name', 'login_id', 'email', 'phone', 'password', 'password_plain', 'role', 'is_active', 'scope_all_regions',
    ];

    protected $hidden = ['password', 'password_plain', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'password_plain' => 'encrypted',
            'is_active' => 'boolean',
            'scope_all_regions' => 'boolean',
        ];
    }

    public function geoScopes(): HasMany
    {
        return $this->hasMany(AdminGeoScope::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class, 'processed_by_admin_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(AdminPermission::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function canModule(string $module, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $action = strtolower($action);
        if (! in_array($action, ['view', 'edit', 'delete'], true)) {
            return false;
        }

        $permission = $this->relationLoaded('permissions')
            ? $this->permissions->firstWhere('module', $module)
            : $this->permissions()->where('module', $module)->first();

        if (! $permission) {
            return false;
        }

        return match ($action) {
            'view' => $permission->can_view,
            'edit' => $permission->can_edit,
            'delete' => $permission->can_delete,
        };
    }

    public static function findForLogin(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        return static::query()
            ->where('login_id', $identifier)
            ->orWhereRaw('LOWER(email) = ?', [strtolower($identifier)])
            ->first();
    }
}
