<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MobileAppUser;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
// use Auth;
use Hash;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{

    protected $auth;

    /*
    public function __construct()
    {
        $this->auth = (new Factory)
            ->withServiceAccount(storage_path('app/firebase_credentials.json'))
            ->createAuth();
    }
    */

    /*
    // Google Sign-in API
    public function googleLogin(Request $request)
    {
        ...
    }
    */

// Apple Login----------------------------------------------------------------------------------------------

    public function appleLogin(Request $request)
    {
        // Step 1: Validate request
        $validator = validator($request->all(), [
            'id_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $identityToken = $request->input('id_token');

        try {
            // Step 2: Decode token header to get 'kid'
            [$headerEncoded] = explode('.', $identityToken);
            $header = json_decode(base64_decode(strtr($headerEncoded, '-_', '+/')), true);
            $kid = $header['kid'] ?? null;

            if (!$kid) {
                return response()->json(['error' => 'Invalid token header: kid not found'], 400);
            }

            // Step 3: Get Apple public keys and parse
            $appleKeys = Http::get('https://appleid.apple.com/auth/keys')->json();
            $jwkKeys = $appleKeys['keys'];
            $publicKeys = JWK::parseKeySet(['keys' => $jwkKeys]);

            // Step 4: Decode and validate token using all keys
            $decoded = JWT::decode($identityToken, $publicKeys);

            // Step 5: Extract Apple ID and email
            $appleId = $decoded->sub ?? null;
            $emailFromToken = $decoded->email ?? null;

            if (!$appleId) {
                return response()->json(['error' => 'Invalid token payload'], 400);
            }

            // Step 6: Fallback to request for name/email (first login only)
            $name = $request->input('full_name', 'Apple User');
            $email = $emailFromToken ?? $request->input('email');

            // Step 7: Find or create user
            $user = MobileAppUser::where('apple_id', $appleId)->first();

            if (!$user) {
                // First-time Apple login
                $user = MobileAppUser::create([
                    'apple_id'     => $appleId,
                    'name'         => $name,
                    'display_name' => $name,
                    'email'        => $email,
                    'login_type'   => 'User',
                ]);
            } else {
                // Optional: Only update missing fields
                $user->update([
                    'email'        => $user->email ?? $email,
                    'name'         => $user->name ?? $name,
                    'display_name' => $user->display_name ?? $name,
                ]);
            }

            // Step 8: Generate Sanctum token
            $token = $user->createToken('apple-login')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user'    => $user,
                'token'   => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Apple Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }




    // Apple Login closes ----------------------------------------------------------------------------------------------------

    /*
    public function verifyOtp(Request $request)
    {
        ...
    }
    */


}
