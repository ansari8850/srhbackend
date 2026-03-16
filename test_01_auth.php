<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

function logTest($name, $response) {
    echo "--------------------------------------------------\n";
    echo "TEST: $name\n";
    echo "STATUS: " . $response->status() . "\n";
    echo "BODY: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
    echo "--------------------------------------------------\n\n";
}

$baseUrl = "http://localhost/api/";

// 1. Mobile Register
$response = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
    'mobile_no' => '9876543210'
]);
logTest("Mobile Register", $response);

// 2. Mobile Login
$response = Http::asJson()->post($baseUrl . 'mobile_login', [
    'email' => 'john@example.com',
    'password' => 'secret123'
]);
logTest("Mobile Login", $response);
$token = $response->json()['access_token'] ?? null;
$userId = $response->json()['result']['id'] ?? null;

// 3. User Profile
if ($userId && $token) {
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'mobile_user_profile', ['id' => $userId]);
    logTest("User Profile", $response);
}

// 4. Update Profile
if ($userId && $token) {
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'mobile_update_profile', [
        'id' => $userId,
        'display_name' => 'John The Hero',
        'bio' => 'Loves health and wellness'
    ]);
    logTest("Update Profile", $response);
}

// 5. Change Password
if ($token) {
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'mobile_change_password', [
        'old_password' => 'secret123',
        'password' => 'newsecret123',
        'confirm_password' => 'newsecret123'
    ]);
    logTest("Change Password", $response);
}

// 6. Forgot Password
$response = Http::asJson()->post($baseUrl . 'mobile_forgot_password', [
    'email' => 'john@example.com'
]);
logTest("Forgot Password", $response);

// 7. Mobile Logout
if ($token) {
    // Note: mobile_logout implementation in PreLoginControllerMobile.php uses $request->user()->token()->revoke()
    // which requires the token to be valid.
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'mobile_logout');
    logTest("Mobile Logout", $response);
}
