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

// 0. Setup: Register & Login to get token
$regRes = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'Master Tester',
    'email' => 'master@example.com',
    'password' => 'secret123',
    'mobile_no' => '1122334455'
]);
$token = $regRes->json()['access_token'] ?? null;

if (!$token) {
    die("Setup failed: Could not get token\n");
}

// 1. Create Master
$response = Http::asJson()->withToken($token)->post($baseUrl . 'master/create/update', [
    'type' => 'Category',
    'name' => 'Health',
    'status' => 'active'
]);
logTest("Create Master", $response);
$masterId = $response->json()['master']['id'] ?? null;

// 2. List Masters
$response = Http::asJson()->withToken($token)->post($baseUrl . 'master/list', [
    'type' => 'Category'
]);
logTest("List Masters", $response);

// 3. Post Type List
$response = Http::asJson()->withToken($token)->post($baseUrl . 'master/post_type_list');
logTest("Post Type List", $response);

// 4. Update Status
if ($masterId) {
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'master/statusupdate', [
        'id' => $masterId,
        'status' => 'inactive'
    ]);
    logTest("Update Master Status", $response);
}

// 5. Delete Master
if ($masterId) {
    $response = Http::asJson()->withToken($token)->post($baseUrl . 'master/delete', [
        'id' => $masterId
    ]);
    logTest("Delete Master", $response);
}
?>
