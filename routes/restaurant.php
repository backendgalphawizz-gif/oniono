<?php

use App\Http\Controllers\Restaurant\AuthController;
use App\Http\Controllers\Restaurant\LocationController;
use App\Http\Controllers\Restaurant\DashboardController;
use App\Http\Controllers\Restaurant\EarningController;
use App\Http\Controllers\Restaurant\FoodItemController;
use App\Http\Controllers\Restaurant\MenuCategoryController;
use App\Http\Controllers\Restaurant\OrderController;
use App\Http\Controllers\Restaurant\ProfileController;
use App\Http\Controllers\Restaurant\ReviewController;
use App\Http\Controllers\Restaurant\SettlementController;
use App\Http\Controllers\Restaurant\HelpController;
use App\Http\Controllers\Restaurant\SupportController;
use App\Http\Controllers\Restaurant\TimingController;
use App\Http\Controllers\Restaurant\SettingController;
use App\Http\Controllers\Restaurant\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:restaurant')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::get('register/locations/cities', [LocationController::class, 'cities'])->name('register.locations.cities');
    Route::post('register/step-1', [AuthController::class, 'saveRegistrationStep1'])->name('register.step1');
    Route::get('register/documents', [AuthController::class, 'showRegisterDocuments'])->name('register.documents');
    Route::get('register/restart', [AuthController::class, 'restartRegistration'])->name('register.restart');
    Route::post('register', [AuthController::class, 'register'])->name('register.submit');
    Route::post('register/otp/send', [AuthController::class, 'sendRegistrationOtp'])->name('register.otp.send');
    Route::post('register/otp/verify', [AuthController::class, 'verifyRegistrationOtp'])->name('register.otp.verify');
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth:restaurant', 'restaurant.approved'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('timings', [TimingController::class, 'edit'])->name('timings.edit');
    Route::put('timings', [TimingController::class, 'update'])->name('timings.update');
    Route::delete('timings/holidays/{holiday}', [TimingController::class, 'destroyHoliday'])->name('timings.holidays.destroy');

    Route::resource('menu-categories', MenuCategoryController::class)->except(['show'])->parameters(['menu-categories' => 'menuCategory']);
    Route::post('menu/{foodItem}/availability', [FoodItemController::class, 'updateAvailability'])->name('menu.availability');
    Route::resource('menu', FoodItemController::class)->except(['show'])->parameters(['menu' => 'foodItem']);
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('earnings', [EarningController::class, 'index'])->name('earnings.index');
    Route::get('settlements', [SettlementController::class, 'index'])->name('settlements.index');
    Route::get('settlements/{settlement}', [SettlementController::class, 'show'])->name('settlements.show');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{panelNotification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('help', [HelpController::class, 'index'])->name('help.index');
    Route::get('help/contact', [HelpController::class, 'contact'])->name('help.contact');
    Route::get('help/pages/{slug}', [HelpController::class, 'page'])->name('help.page');

    Route::get('support', [SupportController::class, 'index'])->name('support.index');
    Route::post('support', [SupportController::class, 'store'])->name('support.store');
    Route::get('support/{support}', [SupportController::class, 'show'])->name('support.show');
    Route::post('support/{support}/reply', [SupportController::class, 'reply'])->name('support.reply');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
});
