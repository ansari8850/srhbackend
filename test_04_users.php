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

// 0. Setup: Register & Login two users to test following
$reg1 = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'User One',
    'email' => 'user1@example.com',
    'password' => 'secret123',
    'mobile_no' => '1111111111'
]);
$token1 = $reg1->json()['access_token'] ?? null;
$id1 = $reg1->json()['user']['id'] ?? null;

$reg2 = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'User Two',
    'email' => 'user2@example.com',
    'password' => 'secret123',
    'mobile_no' => '2222222222'
]);
$token2 = $reg2->json()['access_token'] ?? null;
$id2 = $reg2->json()['user']['id'] ?? null;

if (!$token1 || !$token2) {
    die("Setup failed: Could not get tokens\n");
}

// 1. Create/Update User (Admin-like capability)
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/create/update', [
    'user_type' => 'Professional',
    'field' => 'Cardiology',
    'name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'jane@example.com',
    'mobile_no' => '9988776655'
]);
logTest("Create User", $response);
$janeId = $response->json()['user']['id'] ?? null;

// 2. List Users
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/list', [
    'noofrec' => 10,
    'currentpage' => 1
]);
logTest("List Users", $response);

// 3. User Details
if ($id2) {
    $response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/details', ['id' => $id2]);
    logTest("User Details", $response);
}

// 4. Follow User (User 1 follows User 2)
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/follow', [
    'user_id' => $id2, // User to follow
    'follower_id' => $id1 // The fan
]);
logTest("Follow User", $response);

// 5. Followers List
$response = Http::asJson()->withToken($token1)->post($baseUrl . "user/followers/$id2");
logTest("Followers List", $response);

// 6. Following List
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/following', ['user_id' => $id1]);
logTest("Following List", $response);

// 7. Unfollow User
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/unfollow', [
    'user_id' => $id2,
    'follower_id' => $id1
]);
logTest("Unfollow User", $response);

// 8. Update Status
if ($janeId) {
    $response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/update/status', [
        'id' => $janeId,
        'status' => 'verified'
    ]);
    logTest("Update User Status", $response);
}

// 9. Set FCM Token
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/set/fcmToken', [
    'fcm_token' => 'sample_fcm_token_123'
]);
logTest("Set FCM Token", $response);

// 10. Delete User
if ($janeId) {
    $response = Http::asJson()->withToken($token1)->post($baseUrl . 'user/delete', ['id' => $janeId]);
    logTest("Delete User", $response);
}
?>
