<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Google\Client as Google_Client;
use App\Models\Notification;    
use Auth;
use Log;

class FirebaseService
{
    protected $firebaseProjectId;
    protected $credentialsPath;

    public function __construct()
    {
        $this->firebaseProjectId = env('FIREBASE_PROJECT_ID');
        $this->credentialsPath = storage_path('app/firebase_credentials.json'); // Ensure file exists
    }

    private function getAccessToken()
    {
        $client = new Google_Client();
        $client->setAuthConfig($this->credentialsPath); // Correct JSON credentials path
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithAssertion();
        }

        return $client->getAccessToken()['access_token'];
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$this->firebaseProjectId}/messages:send";

        $payload = [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                // "data" => $data // Ensure this is an associative array
            ]
        ];

        // $this->createNotification($title,$body);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        return $response->json(); // Return JSON response
    }


    // Create Notification to data base
    public function createNotification($user_id,$title,$body){

        // Get the authenticated user
        // $user = Auth::user();

        // Create a new notification
        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->title = $title;
        $notification->body = $body;
        $notification->save();

    }
}
