<?php

$base = 'http://127.0.0.1:8000';

function request(string $url, string $cookieFile, array $opts = []): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        ...$opts,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $code.'|'.substr($body, 0, 200);
}

$cookie = sys_get_temp_dir().'/oniono-cookie.txt';
@unlink($cookie);

$loginPage = file_get_contents($base.'/restaurant/login');
preg_match('/name="_token" value="([^"]+)"/', $loginPage, $m);
$token = $m[1] ?? '';

$post = http_build_query([
    '_token' => $token,
    'login_method' => 'email',
    'identifier' => 'demo@restaurant.test',
    'password' => 'password',
]);

echo "GET login: ".request($base.'/restaurant/login', $cookie).PHP_EOL;
echo "POST login: ".request($base.'/restaurant/login', $cookie, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]).PHP_EOL;
echo "GET dashboard: ".request($base.'/restaurant/dashboard', $cookie).PHP_EOL;
