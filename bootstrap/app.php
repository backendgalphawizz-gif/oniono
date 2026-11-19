<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.active' => \App\Http\Middleware\EnsureAdminActive::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
            'admin.super' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'restaurant.approved' => \App\Http\Middleware\EnsureRestaurantApproved::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            if ($request->is('restaurant/*')) {
                return route('restaurant.login');
            }

            return route('admin.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if (auth('admin')->check()) {
                return admin_home_url();
            }

            if (auth('restaurant')->check()) {
                return route('restaurant.dashboard');
            }

            return route('admin.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
