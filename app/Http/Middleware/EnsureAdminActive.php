<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (! $admin || ! $admin->is_active) {
            auth('admin')->logout();

            return redirect()->route('admin.login')->with('error', 'Admin account is inactive.');
        }

        if ($admin->isEmployee()) {
            $admin->loadMissing('permissions');
        }

        return $next($request);
    }
}
