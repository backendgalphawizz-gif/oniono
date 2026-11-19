<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
class AdminSeeder extends Seeder
{
    /**
     * Default admin login (override via .env if needed):
     *   Admin ID or email:  admin  /  admin@oniono.test
     *   Password:           password
     */
    public function run(): void
    {
        $loginId = env('ADMIN_LOGIN_ID', 'admin');
        $password = env('ADMIN_PASSWORD', 'password');

        Admin::query()->updateOrCreate(
            ['login_id' => $loginId],
            [
                'name' => env('ADMIN_NAME', 'Super Admin'),
                'email' => env('ADMIN_EMAIL', 'admin@oniono.test'),
                'phone' => env('ADMIN_PHONE', '9999999999'),
                'password' => $password,
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
