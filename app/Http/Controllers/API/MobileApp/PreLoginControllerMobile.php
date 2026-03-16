<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\commonHelper;
use Illuminate\Support\Facades\Mail;
use DB; 
use Validator;
use Hash;
use merge;
use Str;
use Carbon\Carbon;
use App\Service\OrganisationService;
use \App\Models\User;
use App\Models\Currency;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use \App\Models\Employee;
use \App\Models\UserPermissions;
use \App\Models\MobileAppUser;
use Illuminate\Support\Facades\Http;

class PreLoginControllerMobile extends Controller
{

    public function mobile_register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:mobile_app_users,email',
            'password' => 'required',
            'mobile_no' => 'required'
        ];

        $validator = Validator::make($request->json()->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 200);
        }

        try {
            $user = MobileAppUser::create([
                'name' => $request->json()->get('name'),
                'email' => $request->json()->get('email'),
                'password' => Hash::make($request->json()->get('password')),
                'mobile_no' => $request->json()->get('mobile_no'),
                'login_type' => 'User',
                'otp_verify' => 1 // Auto-verify for simplicity as requested
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Registration Successful',
                'user' => $user,
                'access_token' => $user->createToken('authToken')->plainTextToken,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 200);
        }
    }

	public function mobile_login(Request $request){
		
		$rules['email'] = 'required';
		$rules['password'] = 'required';
		
		$validator = Validator::make($request->json()->all(), $rules);
		
		if ($validator->fails()) {
			$message = [];
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message= $msg[0];
				break;
			}
			
			return response(array("error"=> true, "message"=>$message),200); 

		}else{

			try{
				
				if($request->json()->get('email')){

					$userResult=MobileAppUser::where([
										['email','=',$request->json()->get('email')]
										])->first();						
				}

				if(!$userResult){
					
					return response(array("error"=> true, "message"=>"Invalid login credentials","status"=>0),200);
					
				}else if(Hash::check($request->json()->get('password'),$userResult->password)==false){
					
					return response(array('message'=>"Password doesn't match.Please try again."),403);
					
				}else{
					// if($userResult->otp_verify=='1' && $request->json()->get('email')){

						return response(array(
							'error'=>false,
							"message"=>'Login Successfully',
							"access_token"=>$userResult->createToken('authToken')->plainTextToken,
							"status"=>1,"verify"=>true,
							"result"=>$userResult,
						),200);

					// }else{
						// return response(array(
						// 	"error"=> true,
						// 	"message"=>"OTP verification is pending so please first verify your account",
						// 	"status"=>2,
						// 	"verify"=>false),
						// 200);
					// }
				}
			}catch (\Exception $e){
				return response(array("error" 
					=> true, "message" => $e->getMessage()),200);
			}
		}
	}
	
	public function mobile_sendOtpOnMail(Request $request)
	{
		$rules = [
			'email' => 'required|email',
		];
	
		$validator = Validator::make($request->json()->all(), $rules);
	
		if ($validator->fails()) {
			$message = [];
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message = $msg[0];
				break;
			}
	
			return response(array('message' => $message), 200);
		} else {
			try {

				$emailResult = MobileAppUser::where([
					['email', '=', $request->json()->get('email')]
				])->first();
	
				if (!$emailResult) {
					return response(array('message' => 'Email id is not registered with us. Please try another email id.'));
				} else {
					$otp = \App\Helpers\commonHelper::getOtp();
	
					MobileAppUser::where([
						['email', '=', $request->json()->get('email')]
					])->update(['otp' => $otp]);
	
					// Ensure MAIL_USERNAME is set
					$mailUsername = env('MAIL_USERNAME');
					if (!$mailUsername) {
						return response(array("error" => true, 
						"message" => "Mail configuration is missing. Please check MAIL_USERNAME in .env file."), 
						200);
					}
	
					$to = $request->json()->get('email');
					// Mail::send('email_templates.otp', compact('otp'), function ($message) use ($to, $mailUsername) {
					// 	$message->from($mailUsername, env('MAIL_FROM_NAME'));
					// 	$message->subject('OTP Verification');
					// 	$message->to($to);
					// });
	
					return response(array('message' => 'OTP sent successfully on your registered Email id.'), 200);
				}
			} catch (\Exception $e) {
				return response(array("error" => true, "message" => $e->getMessage()), 200);
			}
		}
	}
	
	// public function mobile_validateOtp(Request $request){
		
	// 	$rules['email'] = 'required';
	// 	$rules['otp'] = 'required|size:4'; 
		
	// 	$validator = Validator::make($request->json()->all(), $rules);
		
	// 	if ($validator->fails()) {
	// 		$message = [];
	// 		$messages_l = json_decode(json_encode($validator->messages()), true);
	// 		foreach ($messages_l as $msg) {
	// 			$message= $msg[0];
	// 			break;
	// 		}
			
	// 		return response(array("error"=> true, "message"=>$message),200); 

	// 	}else{

	// 		try{
				
	// 			if($request->json()->get('email')){
				
	// 				//chk unique email
	// 				$userResult=MobileAppUser::where([
	// 									['email','=',$request->json()->get('email')],
	// 									['otp','=',$request->json()->get('otp')],
	// 									])->first();
						
	// 			}

	// 			if(!$userResult){
					
	// 				return response(array("error"=> true, "message"=>"OTP doesn't exist. Please try again."),200);

	// 			}else{
					
	// 				$userResult->otp='0';
	// 				$userResult->otp_verify='1';
	// 				$userResult->save();
					
	// 				return response(array("error"=> false, 
	// 					"message"=>"OTP matched successfully.",
	// 					"access_token"=>$userResult->createToken('authToken')->plainTextToken,
	// 					"result"=>$userResult->toArray()),
	// 				200);
					
	// 			}
	// 		}catch (\Exception $e){
				
	// 			return response(array("error" 
	// 					=> true, "message" => $e->getMessage()),200); 
			
	// 		}
	// 	}
	// }
	
	public function mobile_forgotPassword(Request $request){

		$user = MobileAppUser::query();

		if($request['email']!=null && isset($request['email'])){
			$user = $user->where('email', $request->email);
		}

		if($request['mobile_no']!=null && isset($request['mobile_no'])){
			$user = $user->where('mobile_no', $request->mobile_no);
		}

		$user = $user->first();

		if (!$user) {
			return response()->json(['message' => 'User not found'], 404);
		}

		$token = Str::random(60); 

		$obj['c_password'] = Str::random(10);
		$obj['password'] = \Hash::make($obj['c_password']);

		$user->update($obj);

		// Sending new credentials to whatsapp
		$response = $this->sendWhatsAppTemplate($user['name'], $user['email'], $obj['c_password'],$user['mobile_no']);

		DB::table('password_resets')->updateOrInsert([
			'email' => $user->email,
		], [
			'email' => $user->email,
			'token' => $token,
			'created_at' => Carbon::now(),
		]);

		// Mail::send('email_templates.password_reset', ['token' => $token], function ($message) use ($user) {
		// 	$message->to($user->email)->subject('Reset your password');
		// });

		return response()->json([
			'message' => 'Password reset successfully, check your login credentials on whatsapp',
			"token" => $token,
			"user"=>$user,
			"whatsapp_response"=>$response
		], 200);
	}

	function sendWhatsAppTemplate($userName,$username, $password, $mobile_no)
    {
        $url="https://www.srhealthcarecommunity.com/";

        $apiUrl = 'https://api.interakt.ai/v1/public/message/';

        $payload = [
            "countryCode" => "+91",
            "phoneNumber" =>$mobile_no,
            // "fullPhoneNumber" => "919986641508", // Optional
            // "campaignId" => "YOUR_CAMPAIGN_ID", // Not Mandatory
            "callbackData" => "some text here",
            "type" => "Template",
            "template" => [
                "name" => "login_details",
                "languageCode" => "en",
                "bodyValues" => [
                    $userName,
                    $url,
                    $username,
                    $password
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic aXRSZWdZZV9yZ2lscG5nNUpyUzh2b2ZqWl9aX2hWMjRiQWI5NXAtZk81ODo=',
            ])->post($apiUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $response->body(),
                ];
            }
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

	public function mobile_resetPassword(Request $request){

		$rules = [ 
			'password'=>'required',
			"confirm_password"=>"required|same:password"       
		];   

		$validator = Validator::make($request->json()->all(), $rules);
		
		if ($validator->fails()){
			$message = "";
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message= $msg[0];
				break;
			}
			
			return response(array('message'=>$message),403);
			
		}else{
			
			try{
				
				MobileAppUser::where('id',$request->user()->id)
					->update(array(
						// 'c_password'=>$request->json()->get('password'),
						'password'=>Hash::make($request->json()->get('password'),
					)));
					
				return response(array('message'=>"Password updated successfully."),200);
				
				
			}catch (\Exception $e){
				
				return response(array("message" => "Something went wrong.please try again"),403); 
				
			}
	   }
		 
	}

	public function mobile_changePassword(Request $request){

		$rules = [ 
			'old_password'=>'required',
			'password'=>'required',
			"confirm_password"=>"required|same:password"       
		];   

		$validator = Validator::make($request->json()->all(), $rules);
		
		if ($validator->fails()){
			$message = "";
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message= $msg[0];
				break;
			}
			
			return response(array('message'=>$message),403);
			
		}else{
			
			try{
				
				if(Hash::check($request->json()->get('old_password'),$request->user()->password)==false){
					
					return response(array('message'=>"Old password doesn't match.Please try again."),403);
					
				}else{
					
					MobileAppUser::where('id',$request->user()->id)
						->update(array(
							// 'c_password'=>$request->json()->get('password'),
							'password'=>Hash::make($request->json()->get('password'))));
						
					return response(array('message'=>"Password updated successfully."),200);
				}
				
			}catch (\Exception $e){
				
				return response(array("message" => "Something went wrong.please try again"),403); 
				
			}
	   }
		 
	}

	public function mobile_userProfile(Request $request){
 
		try{
			
			$user=MobileAppUser::where('id',$request->id)->first();
				
			return response(array('message'=>"Profile data fetched successfully.","result"=>$user),200);
			
		}catch (\Exception $e){
			return response(array("error"=>true, "message" => $e->getMessage()),200);  
		} 	 
		
	}
	
	public function mobile_updateProfile(Request $request)
	{
		try {
			$user = MobileAppUser::findOrFail($request->id);
	
			$user->update($request->all());
	
			return response(array('message' => 'Profile updated successfully.'), 200);
		} catch (\Exception $e) {
			return response(array("error" => true, "message" => $e->getMessage()), 200);
		}
	}
	
	public function mobile_logout(Request $request){

		$request->user()->currentAccessToken()->delete();

		return response(array('message'=>'Logout successfully.'),200);
	}


	public function getUserbyId(Request $request){

        try{

            $user=User::where('id',$request['enteredbyid'])->first();

            $response=[
                'success'=>true,
                'created_by'=>$user->first_name,
                // 'user'=>$user
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response(['error' => true, 'message' => $e->getMessage()], 404); 
        }
    }

	public function getcountry(Request $request) {

		$countryQuery = Country::query();
	
		if (isset($request['search']) && $request['search'] != '') {
			$keyword = "%" . $request['search'] . "%";
			$countryQuery->whereRaw(
				"(name LIKE ? OR numeric_code LIKE ? OR id LIKE ? OR currency LIKE ? OR currency_name LIKE ? OR phonecode LIKE ?)",
				[$keyword, $keyword, $keyword, $keyword, $keyword, $keyword]
			);
		}
	
		$countries = $countryQuery->get();
	
		$response = [
			'success' => true,
			'country' => $countries
		];
	
		return response()->json($response);
	}
	

    public function getstate(Request $request) {
		// Validate Item
		$validator = validator($request->all(), [
			'country_id' => 'required',
		]);
	
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first()
			]);
		} else {
			// Build the initial query
			$stateQuery = State::where('country_id', $request['country_id'])
							   ->select('id', 'name')
							   ->orderBy('name', 'Asc');
	
			// Apply search filter if provided
			if (isset($request['search']) && $request['search'] != '') {
				$keyword = "%" . $request['search'] . "%";
				$stateQuery->where('name', 'LIKE', $keyword);
			}
	
			// Get the results
			$states = $stateQuery->get();
	
			$response = [
				'success' => true,
				'country' => $states
			];
	
			return response()->json($response);
		}
	}
	

    public function getcity(Request $request) {
		// Validate Item
		$validator = validator($request->all(), [
			'state_id' => 'required',
		]);
	
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first()
			]);
		} else {
			// Build the initial query
			$cityQuery = City::where('state_id', $request['state_id'])
							 ->select('id', 'name')
							 ->orderBy('name', 'Asc');
	
			// Apply search filter if provided
			if (isset($request['search']) && $request['search'] != '') {
				$keyword = "%" . $request['search'] . "%";
				$cityQuery->where('name', 'LIKE', $keyword);
			}
	
			// Get the results
			$cities = $cityQuery->get();
	
			$response = [
				'success' => true,
				'country' => $cities
			];
	
			return response()->json($response);
		}
	}
	
    public function getAllCurrency(Request $request){
        $currency=Currency::get();

        $response=[
            'success'=>true,
            'currency'=>$currency
        ];

        return response()->json($response);
    }

	public function getcountryname(Request $request){

        // Validate Item
        $validator=validator($request->all(),[
            'country_id'=>'required',
        ]);

        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first()
            ];
        } else {
            $country=Country::where('id',$request['country_id'])->select('id','name')
                ->orderBy('name','Asc')
                ->get();

            $response=[
                'success'=>true,
                'country'=>$country
            ];

            return response()->json($response);
        }
    }
	
	public function mobile_admin_login(Request $request){
		
		$rules['email'] = 'required';
		$rules['password'] = 'required';
		
		$validator = Validator::make($request->json()->all(), $rules);
		
		if ($validator->fails()) {
			$message = [];
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message= $msg[0];
				break;
			}
			
			return response(array("error"=> true, "message"=>$message),200); 

		}else{

			try{
				
				if($request->json()->get('email')){

					$userResult=MobileAppUser::where([
							['email','=',$request->json()->get('email')]
						])
						->where('login_type','Admin')->first();						
				}

				if(!$userResult){
					
					return response(array("error"=> true, "message"=>"Invalid login credentials","status"=>0),200);
					
				}else if(Hash::check($request->json()->get('password'),$userResult->password)==false){
					
					return response(array('message'=>"Password doesn't match.Please try again."),403);
					
				}else{
					if($userResult->login_type=='Admin' && $request->json()->get('email')){

						return response(array(
							'error'=>false,
							"message"=>'Login Successfully',
							"access_token"=>$userResult->createToken('authToken')->plainTextToken,
							"status"=>1,"verify"=>true,
							"result"=>$userResult,
						),200);

					}else{
						return response(array("error"=> true, "message"=>"Invalid login credentials","status"=>0),200);
					}
				}
			}catch (\Exception $e){
				return response(array("error" 
					=> true, "message" => $e->getMessage()),200);
			}
		}
	}
	
}
