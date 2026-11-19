<?php

namespace App\Support;

use Illuminate\Http\Request;

class AdminRoutePermission
{
    /**
     * @return array{module: string, action: string}|null
     */
    public static function resolve(Request $request): ?array
    {
        $routeName = $request->route()?->getName();
        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        if (in_array($routeName, config('admin_permissions.exempt_routes', []), true)) {
            return null;
        }

        foreach (config('admin_permissions.super_admin_only_route_prefixes', []) as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return null;
            }
        }

        $module = self::moduleForRoute($routeName);
        if ($module === null || $module === '') {
            return null;
        }

        $overrides = config('admin_permissions.action_overrides', []);
        if (isset($overrides[$routeName])) {
            $action = $overrides[$routeName];
        } else {
            $action = self::actionForMethod($request->method());
        }

        return ['module' => $module, 'action' => $action];
    }

    public static function moduleForRoute(string $routeName): ?string
    {
        $prefixes = config('admin_permissions.route_prefixes', []);
        uksort($prefixes, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($prefixes as $prefix => $module) {
            if ($module === null) {
                continue;
            }
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return $module;
            }
        }

        return null;
    }

    public static function actionForMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'DELETE' => 'delete',
            'POST', 'PUT', 'PATCH' => 'edit',
            default => 'view',
        };
    }

    public static function isSuperAdminOnlyRoute(?string $routeName): bool
    {
        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        foreach (config('admin_permissions.super_admin_only_route_prefixes', []) as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return true;
            }
        }

        return false;
    }
}
