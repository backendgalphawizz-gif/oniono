<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(SiteSettingsService::class)->seedDefaults();

        $defaults = [
            ['key' => 'default_commission_percentage', 'value' => '10', 'type' => 'decimal', 'group' => 'commission'],
            ['key' => 'default_delivery_partner_commission_percentage', 'value' => '15', 'type' => 'decimal', 'group' => 'commission'],
            ['key' => 'default_gst_percentage', 'value' => '5', 'type' => 'decimal', 'group' => 'commission'],
            ['key' => 'commission_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'commission'],
            ['key' => 'cod_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'payment'],
            ['key' => 'online_payment_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'payment'],
        ];

        foreach ($defaults as $row) {
            Setting::query()->updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
