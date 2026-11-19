<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Support\AdminRoutePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        if (! $admin) {
            return $next($request);
        }

        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (AdminRoutePermission::isSuperAdminOnlyRoute(is_string($routeName) ? $routeName : null)) {
            abort(403, 'You do not have permission to access this area.');
        }

        if (is_string($routeName) && in_array($routeName, config('admin_permissions.exempt_routes', []), true)) {
            return $next($request);
        }

        $resolved = AdminRoutePermission::resolve($request);

        if ($resolved === null) {
            abort(403, 'You do not have permission to access this area.');
        }

        if (! $admin->canModule($resolved['module'], $resolved['action'])) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
