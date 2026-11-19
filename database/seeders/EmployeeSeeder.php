<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Services\AdminPermissionSyncService;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Demo staff accounts (same login page as Super Admin: /admin/login).
     *
     * | Admin ID    | Email                  | Password | Purpose                          |
     * |-------------|------------------------|----------|----------------------------------|
     * | staff_orders| staff.orders@oniono.test | password | Orders + delivery (view/edit)    |
     * | staff_menu  | staff.menu@oniono.test   | password | Food menu modules (view/edit)    |
     * | staff_read  | staff.read@oniono.test   | password | Finance view-only (no edit)      |
     */
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD', 'password');
        $sync = app(AdminPermissionSyncService::class);

        $profiles = [
            [
                'login_id' => env('STAFF_ORDERS_LOGIN_ID', 'staff_orders'),
                'email' => env('STAFF_ORDERS_EMAIL', 'staff.orders@oniono.test'),
                'name' => 'Demo Staff (Orders)',
                'phone' => '8888800001',
                'permissions' => [
                    'dashboard' => ['view' => true, 'edit' => false, 'delete' => false],
                    'orders' => ['view' => true, 'edit' => true, 'delete' => false],
                    'delivery_assignment' => ['view' => true, 'edit' => true, 'delete' => false],
                ],
            ],
            [
                'login_id' => env('STAFF_MENU_LOGIN_ID', 'staff_menu'),
                'email' => env('STAFF_MENU_EMAIL', 'staff.menu@oniono.test'),
                'name' => 'Demo Staff (Menu)',
                'phone' => '8888800002',
                'permissions' => [
                    'dashboard' => ['view' => true, 'edit' => false, 'delete' => false],
                    'menu_management' => ['view' => true, 'edit' => true, 'delete' => false],
                    'categories' => ['view' => true, 'edit' => true, 'delete' => true],
                    'sub_categories' => ['view' => true, 'edit' => true, 'delete' => false],
                    'cuisines' => ['view' => true, 'edit' => true, 'delete' => false],
                    'food_items' => ['view' => true, 'edit' => true, 'delete' => false],
                ],
            ],
            [
                'login_id' => env('STAFF_READ_LOGIN_ID', 'staff_read'),
                'email' => env('STAFF_READ_EMAIL', 'staff.read@oniono.test'),
                'name' => 'Demo Staff (Read-only)',
                'phone' => '8888800003',
                'permissions' => [
                    'payments' => ['view' => true, 'edit' => false, 'delete' => false],
                    'settlements' => ['view' => true, 'edit' => false, 'delete' => false],
                    'commissions' => ['view' => true, 'edit' => false, 'delete' => false],
                ],
            ],
        ];

        foreach ($profiles as $profile) {
            $admin = Admin::query()->updateOrCreate(
                ['login_id' => $profile['login_id']],
                [
                    'name' => $profile['name'],
                    'email' => $profile['email'],
                    'phone' => $profile['phone'],
                    'password' => $password,
                    'password_plain' => $password,
                    'role' => 'employee',
                    'is_active' => true,
                ]
            );

            $sync->sync($admin, $profile['permissions']);
        }
    }
}
