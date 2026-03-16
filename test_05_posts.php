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

// 0. Setup: Register & Login two users
$reg1 = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'Post Owner',
    'email' => 'owner@example.com',
    'password' => 'secret123',
    'mobile_no' => '9111111111'
]);
$token1 = $reg1->json()['access_token'] ?? null;
$id1 = $reg1->json()['user']['id'] ?? null;

$reg2 = Http::asJson()->post($baseUrl . 'mobile_register', [
    'name' => 'Post Liker',
    'email' => 'liker@example.com',
    'password' => 'secret123',
    'mobile_no' => '9222222222'
]);
$token2 = $reg2->json()['access_token'] ?? null;
$id2 = $reg2->json()['user']['id'] ?? null;

if (!$token1 || !$token2) {
    die("Setup failed: Could not get tokens\n");
}

// 1. Create Post
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'post/create/update', [
    'user_id' => $id1,
    'user_name' => 'Post Owner',
    'title' => 'My First Post',
    'description' => 'Hello Community!',
    'post_type' => 'Update',
    'status' => 'active'
]);
logTest("Create Post", $response);
$postId = $response->json()['post']['id'] ?? null;

// 2. List Posts (Viewer 2 sees it)
$response = Http::asJson()->withToken($token2)->post($baseUrl . 'post/list', [
    'noofrec' => 10,
    'currentpage' => 1
]);
logTest("List Posts", $response);

// 3. Post Details
if ($postId) {
    $response = Http::asJson()->withToken($token2)->post($baseUrl . 'post/details', ['id' => $postId]);
    logTest("Post Details", $response);
}

// 4. Bookmark Post (User 2 bookmarks User 1's post)
if ($postId) {
    $response = Http::asJson()->withToken($token2)->post($baseUrl . 'post/bookmark/add', [
        'user_id' => $id2,
        'post_id' => $postId
    ]);
    logTest("Add Bookmark", $response);
}

// 5. User Bookmarks List
$response = Http::asJson()->withToken($token2)->post($baseUrl . 'post/bookmark/user', ['user_id' => $id2]);
logTest("User Bookmarks", $response);

// 6. Report Post (User 2 reports User 1's post)
if ($postId) {
    $response = Http::asJson()->withToken($token2)->post($baseUrl . 'post/reported', [
        'user_id' => $id2, // Reporter
        'post_id' => $postId,
        'reason' => 'Spam'
    ]);
    logTest("Report Post", $response);
}

// 7. Reported Posts List
$response = Http::asJson()->withToken($token1)->post($baseUrl . 'post/reported/list');
logTest("Reported Posts List", $response);

// 8. Delete Post
if ($postId) {
    $response = Http::asJson()->withToken($token1)->post($baseUrl . 'post/delete', ['id' => $postId]);
    logTest("Delete Post", $response);
}
?>
