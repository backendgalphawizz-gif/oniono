<?php

namespace Database\Seeders;

use App\Services\CmsStaticPageService;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        app(CmsStaticPageService::class)->ensurePagesExist();
    }
}
