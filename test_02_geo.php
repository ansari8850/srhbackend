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

// 1. Get All Currency
$response = Http::asJson()->post($baseUrl . 'getAllCurrency');
logTest("Get All Currency", $response);

// 2. Get Country
$response = Http::asJson()->post($baseUrl . 'getcountry', ['search' => 'India']);
logTest("Get Country", $response);
$countryId = $response->json()['country'][0]['id'] ?? null;

// 3. Get State
if ($countryId) {
    $response = Http::asJson()->post($baseUrl . 'getstate', ['country_id' => $countryId, 'search' => 'Maharashtra']);
    logTest("Get State", $response);
    $stateId = $response->json()['country'][0]['id'] ?? null;

    // 4. Get City
    if ($stateId) {
        $response = Http::asJson()->post($baseUrl . 'getcity', ['state_id' => $stateId, 'search' => 'Mumbai']);
        logTest("Get City", $response);
    }
}

// 5. Get Country Name
if ($countryId) {
    $response = Http::asJson()->post($baseUrl . 'getcountryname', ['country_id' => $countryId]);
    logTest("Get Country Name", $response);
}
?>
