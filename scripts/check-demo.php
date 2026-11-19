<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = App\Models\Admin::where('email', 'admin@oniono.test')->first();
$rest = App\Models\RestaurantUser::where('email', 'demo@restaurant.test')->first();

echo 'admin: '.($admin ? 'yes' : 'no').PHP_EOL;
echo 'restaurant user: '.($rest ? 'yes status='.$rest->restaurant->status->value : 'no').PHP_EOL;
