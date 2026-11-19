<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StateCitySeeder::class,
            AdminSeeder::class,
            EmployeeSeeder::class,
            SettingsSeeder::class,
            StaticPageSeeder::class,
            CategorySeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
