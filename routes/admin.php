<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CuisineController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryAssignmentController;
use App\Http\Controllers\Admin\DeliveryPartnerController;
use App\Http\Controllers\Admin\FoodItemController;
use App\Http\Controllers\Admin\MenuManagementController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportAnalyticsController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\MigrationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth:admin', 'admin.active', 'admin.permission'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/order-activities', [DashboardController::class, 'orderActivities'])->name('dashboard.order-activities');
    Route::get('dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    Route::resource('restaurants', RestaurantController::class);
    Route::post('restaurants/{restaurant}/approve', [RestaurantController::class, 'approve'])->name('restaurants.approve');
    Route::post('restaurants/{restaurant}/reject', [RestaurantController::class, 'reject'])->name('restaurants.reject');
    Route::post('restaurants/{restaurant}/suspend', [RestaurantController::class, 'suspend'])->name('restaurants.suspend');
    Route::post('restaurants/{restaurant}/activate', [RestaurantController::class, 'activate'])->name('restaurants.activate');
    Route::post('restaurants/{restaurant}/block', [RestaurantController::class, 'block'])->name('restaurants.block');
    Route::post('restaurants/{restaurant}/unblock', [RestaurantController::class, 'unblock'])->name('restaurants.unblock');
    Route::post('restaurants/documents/{document}/verify', [RestaurantController::class, 'verifyDocument'])->name('restaurants.documents.verify');
    Route::post('restaurants/documents/{document}/reject', [RestaurantController::class, 'rejectDocument'])->name('restaurants.documents.reject');

    Route::resource('users', UserController::class);
    Route::post('users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::post('users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('users/{user}/unverify', [UserController::class, 'unverify'])->name('users.unverify');

    Route::get('menu-management', [MenuManagementController::class, 'index'])->name('menu-management.index');
    Route::get('menu-management/featured', [MenuManagementController::class, 'featured'])->name('menu-management.featured');
    Route::post('menu-management/restaurants/{restaurant}/featured', [MenuManagementController::class, 'toggleRestaurantFeatured'])->name('menu-management.restaurants.featured');
    Route::post('menu-management/food-items/{foodItem}/featured', [MenuManagementController::class, 'toggleFoodFeatured'])->name('menu-management.food-items.featured');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('sub-categories', SubCategoryController::class)
        ->parameters(['sub-categories' => 'subCategory'])
        ->except(['show']);
    Route::resource('cuisines', CuisineController::class)->except(['show']);
    Route::resource('food-items', FoodItemController::class);

    Route::resource('delivery-partners', DeliveryPartnerController::class);
    Route::post('delivery-partners/{delivery_partner}/approve', [DeliveryPartnerController::class, 'approve'])->name('delivery-partners.approve');
    Route::post('delivery-partners/{delivery_partner}/reject', [DeliveryPartnerController::class, 'reject'])->name('delivery-partners.reject');
    Route::post('delivery-partners/{delivery_partner}/block', [DeliveryPartnerController::class, 'block'])->name('delivery-partners.block');
    Route::post('delivery-partners/{delivery_partner}/unblock', [DeliveryPartnerController::class, 'unblock'])->name('delivery-partners.unblock');
    Route::post('delivery-partners/{delivery_partner}/suspend', [DeliveryPartnerController::class, 'suspend'])->name('delivery-partners.suspend');
    Route::post('delivery-partners/{delivery_partner}/unsuspend', [DeliveryPartnerController::class, 'unsuspend'])->name('delivery-partners.unsuspend');
    Route::post('delivery-partners/documents/{document}/verify', [DeliveryPartnerController::class, 'verifyDocument'])->name('delivery-partners.documents.verify');
    Route::post('delivery-partners/documents/{document}/reject', [DeliveryPartnerController::class, 'rejectDocument'])->name('delivery-partners.documents.reject');

    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('delivery-assignment', [DeliveryAssignmentController::class, 'index'])->name('delivery-assignment.index');
    Route::post('delivery-assignment/{order}/assign', [DeliveryAssignmentController::class, 'assign'])->name('delivery-assignment.assign');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/cod-collect', [PaymentController::class, 'markCodCollected'])->name('payments.cod-collect');

    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('commissions/global', [CommissionController::class, 'updateGlobal'])->name('commissions.global');
    Route::post('commissions/restaurant/{restaurant}', [CommissionController::class, 'updateRestaurant'])->name('commissions.restaurant');
    Route::post('commissions/delivery-partner/{deliveryPartner}', [CommissionController::class, 'updateDeliveryPartner'])->name('commissions.delivery-partner');

    Route::get('settlements', [SettlementController::class, 'index'])->name('settlements.index');
    Route::get('settlements/create/{restaurant}', [SettlementController::class, 'create'])->name('settlements.create');
    Route::post('settlements/{restaurant}', [SettlementController::class, 'store'])->name('settlements.store');
    Route::get('settlements/{settlement}', [SettlementController::class, 'show'])->name('settlements.show');

    Route::resource('coupons', CouponController::class)->except(['show']);
    Route::resource('banners', BannerController::class)->except(['show']);
    Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);

    Route::get('support', [SupportController::class, 'index'])->name('support.index');
    Route::get('support/{support}', [SupportController::class, 'show'])->name('support.show');
    Route::post('support/{support}/reply', [SupportController::class, 'reply'])->name('support.reply');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{panelNotification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::redirect('reports', '/admin/reports-analytics/orders')->name('reports.index');
    Route::prefix('reports-analytics')->name('reports-analytics.')->group(function () {
        Route::get('orders', [ReportAnalyticsController::class, 'orders'])->name('orders');
        Route::get('revenue', [ReportAnalyticsController::class, 'revenue'])->name('revenue');
        Route::get('user-activity', [ReportAnalyticsController::class, 'userActivity'])->name('user-activity');
        Route::get('restaurant-performance', [ReportAnalyticsController::class, 'restaurantPerformance'])->name('restaurant-performance');
        Route::get('delivery-partner-performance', [ReportAnalyticsController::class, 'deliveryPartnerPerformance'])->name('delivery-partner-performance');
        Route::get('popular', [ReportAnalyticsController::class, 'popular'])->name('popular');
        Route::get('order-trends', [ReportAnalyticsController::class, 'orderTrends'])->name('order-trends');
    });
    Route::get('settings', fn () => redirect()->route('admin.settings.general'))->name('settings.index');
    Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
    Route::get('settings/site', [SettingController::class, 'site'])->name('settings.site');
    Route::get('settings/migrations', [MigrationController::class, 'index'])->name('settings.migrations');
    Route::get('settings/cms', [StaticPageController::class, 'index'])->name('settings.cms.index');
    Route::get('settings/cms/{slug}', [StaticPageController::class, 'edit'])->name('settings.cms.edit');
    Route::put('settings/cms/{slug}', [StaticPageController::class, 'update'])->name('settings.cms.update');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/site', [SettingController::class, 'updateSite'])->name('settings.site.update');
    Route::post('settings/migrations/run-all', [MigrationController::class, 'runAll'])->name('settings.migrations.run-all');
    Route::post('settings/migrations/run-group/{group}', [MigrationController::class, 'runGroup'])->name('settings.migrations.run-group');

    Route::prefix('settings/locations')->name('settings.locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('states/create', [LocationController::class, 'createState'])->name('states.create');
        Route::post('states', [LocationController::class, 'storeState'])->name('states.store');
        Route::get('states/{state}/edit', [LocationController::class, 'editState'])->name('states.edit');
        Route::put('states/{state}', [LocationController::class, 'updateState'])->name('states.update');
        Route::delete('states/{state}', [LocationController::class, 'destroyState'])->name('states.destroy');
        Route::get('cities/create', [LocationController::class, 'createCity'])->name('cities.create');
        Route::post('cities', [LocationController::class, 'storeCity'])->name('cities.store');
        Route::get('cities/{city}/edit', [LocationController::class, 'editCity'])->name('cities.edit');
        Route::put('cities/{city}', [LocationController::class, 'updateCity'])->name('cities.update');
        Route::delete('cities/{city}', [LocationController::class, 'destroyCity'])->name('cities.destroy');
    });

    Route::middleware('admin.super')->prefix('settings/employees')->name('settings.employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('{admin}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('{admin}', [EmployeeController::class, 'update'])->name('update');
        Route::post('{admin}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('{admin}', [EmployeeController::class, 'destroy'])->name('destroy');
    });
});
