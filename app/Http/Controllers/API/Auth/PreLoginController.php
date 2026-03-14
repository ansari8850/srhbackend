<?php

namespace App\Http\Controllers\API\Auth;

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

class PreLoginController extends Controller
{

    public function emailRegistration(Request $request){
		
		$rules = [
            'first_name' => 'required', 
            'email' => 'required|unique:users,email',
            'mobile' => 'required',
			'company_name' => 'required',
            'no_of_employee' => 'required', 
            'country' => 'required',
            'password' => 'required'
		];

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

			try {

				$email = $request->json()->get('email');
	
				$user = User::where('email', $email)->first();

				if($user) {
					
					return response(['error' => true, 'message' => 'Email is already registered. Please try another email.'], 200);
					
				} else {

					$otp = \App\Helpers\commonHelper::getOtp();

					$random_string = Str::random(8);
            		// $organisationID = strtoupper($random_string);
					
					$user = new User();
					$user->email = $email;
					// $user->organisation_id = $organisationID;
					$user->first_name = $request->json()->get('first_name');
					$user->mobile = $request->json()->get('mobile');
					$user->password = Hash::make($request->json()->get('password'));
					$user->otp = $otp;
					// $user->save();
	
                    // Creating Organization
                    // $organisation=new OrganisationService;
                    // $organisation->organisationCreate($request,$user);

					// Send OTP to email
					$to = $email;
					Mail::send('email_templates.otp', compact('otp'), function($message) use ($to) {
						$message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
						$message->subject('OTP Verification');
						$message->to($to);
					});
	
					return response(['error' => false, 'message' => 'Registration Successful. OTP sent to your email for verification'], 200); 
				}

			} catch (\Exception $e) {
				return response(['error' => true, 'message' => $e->getMessage()], 200); 
			}
		}
	}
	
	public function sendOtpOnMail(Request $request){
		
		$rules = [
            'email' => 'required|email', 
		];

		$validator = Validator::make($request->json()->all(), $rules);
		 
		if ($validator->fails()) {
			$message = [];
			$messages_l = json_decode(json_encode($validator->messages()), true);
			foreach ($messages_l as $msg) {
				$message= $msg[0];
				break;
			}
			
			return response(array('message'=>$message),200);

		}else{

			try{
				
				//chk unique email
				$emailResult=User::where([
										['email','=',$request->json()->get('email')]
										])->first();
										
				if(!$emailResult){
					
					return response(array('message'=>'Email id is not registered with us. Please try another email id.'));
				
				}elseif($emailResult->is_employee==0){
					
					return response(array('message'=>'Invalid login credentials So please contact your administrator.'),200);
				
				}else{
					
					$otp=\App\Helpers\commonHelper::getOtp();
					
					User::where([
								['email','=',$request->json()->get('email')]
								])->update(['otp'=>$otp]);
													
					$to=$request->json()->get('email');
					Mail::send('email_templates.otp', compact('otp'), function($message) use ($to) {
						$message->from(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
						$message->subject('OTP Verification');
						$message->to($to);
					});
					 	
					return response(array('message'=>'OTP sent successfully on your registered Email id.'),200);
					
				}
				
			}catch (\Exception $e){
				
				return response(array("error" 
						=> true, "message" => $e->getMessage()),200); 
			
			}
		}
		
	}
	
	public function validateOtp(Request $request){
		
		$rules['email'] = 'required';
		$rules['otp'] = 'required|size:4'; 
		
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
				
					//chk unique email
					$userResult=User::where([
										['email','=',$request->json()->get('email')],
										['otp','=',$request->json()->get('otp')],
										])->first();
						
				}

				if(!$userResult){
					
					return response(array("error"=> true, "message"=>"OTP doesn't exist. Please try again."),200);

				}elseif($userResult->is_employee==0){
					
					return response(array("error"=> false, 
						"message"=>'Invalid login credentials so please contact your administrator.'),
					200);
				
				}else{
					
					$userResult->otp='0';
					$userResult->otp_verify='1';
					$userResult->save();
					
					return response(array("error"=> false, 
						"message"=>"OTP matched successfully.",
						"access_token"=>$userResult->createToken('authToken')->plainTextToken,
						"result"=>$userResult->toArray()),
					200);
					
				}
			}catch (\Exception $e){
				
				return response(array("error" 
						=> true, "message" => $e->getMessage()),200); 
			
			}
		}
	}

	public function login(Request $request){
		
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

					$userResult=User::where([
										['email','=',$request->json()->get('email')]
										])->first();						
				}

				if(!$userResult){
					
					return response(array("error"=> true, "message"=>"Invalid login credentials","status"=>0),200);
					
				}else if(Hash::check($request->json()->get('password'),$userResult->password)==false){
					
					return response(array('message'=>"Password doesn't match.Please try again."),403);
					
				}else if($userResult->is_employee=='0' || $userResult->is_disabled==1){
					
					return response(array(
						"error"=> true, 
						"message"=>"Employee not found."), 
						404);
				
				}else{
					
					if($userResult->otp_verify=='1' && $request->json()->get('email')){

						$empResult=Employee::where([
							['email','=',$request->json()->get('email')]
							])->first();

						$permission=UserPermissions::where('role_id',$userResult->role_id)->first();

						return response(array(
							'error'=>false,
							"message"=>'Login Successfully',
							"access_token"=>$userResult->createToken('authToken')->plainTextToken,
							"status"=>1,"verify"=>true,
							"result"=>$empResult->toArray(),
							"user_permission"=>$permission,),
						200);

					}else{
						return response(array(
							"error"=> true,
							"message"=>"OTP verification is pending so please first verify your account",
							"status"=>2,
							"verify"=>false),
						200);
						
					}
				}
			}catch (\Exception $e){
				return response(array("error" 
					=> true, "message" => $e->getMessage()),200);
			}
		}
	}
	
	public function forgotPassword(Request $request){

		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
		]);

		if ($validator->fails()) {
			return response()->json(['message' => $validator->errors()->first()], 422);
		}

		$user = User::where('email', $request->email)->first();

		if (!$user) {
			return response()->json(['message' => 'User not found'], 404);
		}

		$token = Str::random(60); 

		DB::table('password_resets')->updateOrInsert([
			'email' => $user->email,
		], [
			'email' => $user->email,
			'token' => $token,
			'created_at' => Carbon::now(),
		]);

		Mail::send('email_templates.password_reset', ['token' => $token], function ($message) use ($user) {
			$message->to($user->email)->subject('Reset your password');
		});

		return response()->json(['message' => 'Password reset link sent to your email',"token" => $token], 200);
	}

	public function resetPassword(Request $request){

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
				
				User::where('id',$request->user()->id)
					->update(array('password'=>Hash::make($request->json()->get('password'))));
					
				return response(array('message'=>"Password updated successfully."),200);
				
				
			}catch (\Exception $e){
				
				return response(array("message" => "Something went wrong.please try again"),403); 
				
			}
	   }
		 
	}

	public function changePassword(Request $request){

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
					
					User::where('id',$request->user()->id)
						->update(array('password'=>Hash::make($request->json()->get('password'))));
						
					return response(array('message'=>"Password updated successfully."),200);
				}
				
			}catch (\Exception $e){
				
				return response(array("message" => "Something went wrong.please try again"),403); 
				
			}
	   }
		 
	}

	public function userProfile(Request $request){
 
		try{
				
				$imagePath = "";
				if(!empty($request->user()->image)){
				  $imagePath = 	asset('uploads/users/'.$request->user()->image);
				}
				$result = [
					'name'=>$request->user()->name,
					'email'=>$request->user()->email,
					'status'=>$request->user()->status,
					'address'=>$request->user()->address,
					'image'=>$imagePath

				];
			
			return response(array('message'=>"Profile data fetched successfully.","result"=>$result),200);

			
		}catch (\Exception $e){
			
			return response(array("error"=>true, "message" => $e->getMessage()),200);  
		
		} 	 
		
	}
	
	public function updateProfile(Request $request){
	
		try{
			
				$user= User::find($request->user()->id);
				
				if($request->hasFile('image')){
					$imageData = $request->file('image');
					$image = strtotime(date('Y-m-d H:i:s')).'.'.$imageData->getClientOriginalExtension();
					$destinationPath = public_path('/uploads/users');
					$imageData->move($destinationPath, $image);
					
					$user->profileimage=$image;
				}

				$user->name=$request->post('name');
				$user->email=$request->post('email');
				$user->address=$request->post('address');
				$user->save();
				
				return response(array('message'=>'Profile updated successfully.'),200);
				
			
		}catch (\Exception $e){
			
			return response(array("error"=>true, "message" => $e->getMessage()),200); 
			
		}
	}
	
	public function logout(Request $request){

		$request->user()->token()->revoke();

		return response(array('message'=>'Logout successfully.'),200);
	}

	public function getUserbyId(Request $request){

        try{

            $user=User::where('id',$request['enteredbyid'])->first();

            $response=[
                'success'=>true,
                'created_by'=>$user->first_name
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
}
