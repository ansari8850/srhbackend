<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\SearchHistory;
use App\Models\Notification;

function logTest($name, $response) {
    echo "--------------------------------------------------\n";
    echo "TEST: $name\n";
    echo "STATUS: " . $response->status() . "\n";
    echo "BODY: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
    echo "--------------------------------------------------\n\n";
}

$baseUrl = "http://localhost/api/";

// 1. Setup: Register & Login
$reg = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'Misc Tester',
    'email' => 'misc@example.com',
    'password' => 'secret123',
    'mobile_no' => '9999999999'
]);
$token = $reg->json()['access_token'] ?? null;
$userId = $reg->json()['user']['id'] ?? null;

if (!$token) {
    die("Setup failed: Could not get token\n");
}

// 2. Seed Search History & Notifications via DB (since there's no direct create API for some)
SearchHistory::create(['user_id' => $userId, 'search_word' => 'PHP Laravel']);
Notification::create(['user_id' => $userId, 'title' => 'Welcome', 'body' => 'Welcome to SR Health']);

// 3. List Search History
$response = Http::asJson()->withToken($token)->post($baseUrl . 'search/history/list', ['user_id' => $userId]);
logTest("List Search History", $response);

// 4. Clear Search History
$response = Http::asJson()->withToken($token)->post($baseUrl . 'search/history/clear', ['user_id' => $userId]);
logTest("Clear Search History", $response);

// 5. List Notifications
$response = Http::asJson()->withToken($token)->post($baseUrl . 'notification/list', [
    'user_id' => $userId,
    'currentpage' => 1,
    'noofrec' => 10
]);
logTest("List Notifications", $response);

// 6. Subscription Plans List
$response = Http::asJson()->withToken($token)->post($baseUrl . 'subscription/plan/list');
logTest("Subscription Plans List", $response);

?>
