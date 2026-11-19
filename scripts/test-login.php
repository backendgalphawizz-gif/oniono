<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$credentials = ['email' => 'demo@restaurant.test', 'password' => 'password'];
$ok = Auth::guard('restaurant')->attempt($credentials);
echo $ok ? "restaurant login ok\n" : "restaurant login failed\n";

$admin = App\Models\Admin::findForLogin('admin');
$ok2 = $admin && Illuminate\Support\Facades\Hash::check('password', $admin->password);
echo $ok2 ? "admin login (id) ok\n" : "admin login (id) failed\n";

$adminEmail = App\Models\Admin::findForLogin('admin@oniono.test');
$ok3 = $adminEmail && Illuminate\Support\Facades\Hash::check('password', $adminEmail->password);
echo $ok3 ? "admin login (email) ok\n" : "admin login (email) failed\n";
