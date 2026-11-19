<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth('admin')->check()) {
        return redirect(admin_home_url());
    }

    if (auth('restaurant')->check()) {
        return redirect()->route('restaurant.dashboard');
    }

    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    require base_path('routes/admin.php');
});

Route::prefix('restaurant')->name('restaurant.')->group(function () {
    require base_path('routes/restaurant.php');
});
