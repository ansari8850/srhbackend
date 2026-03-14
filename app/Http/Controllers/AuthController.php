<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\JWT\Error\IdTokenVerificationFailed;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Auth;
use App\Models\User;
use Hash;
// use App\Models\UserMobile;
use Log;
use Validator;
use App\Service\GeneratedIdService;
use App\Service\AddressService;
use App\Service\SMS\SMSService;
use App\Events\SmsEvent;

class AuthController extends Controller
{

	public function verifytoken(Request $request){
		return response()->json(auth('sanctum')->check());
	}

	public function register(Request $request){
		$response = [
			'access_token'=>null,
			'user'=>null,
			'success' => false,
			'message' => '',
			'redirect'=>''
		];
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
			'mobileno'=>['required','min:10'],
            'password' => ['required', 'string', 'min:3']
			// 'pincode'=>['required','integer','min:6'],
        ]);
        if($validator->fails()){
			//if mobile has prefix + then match the complete mobile
			
            return response()->json($validator->errors(), 422);
        }

		// register customer
        $obj['firstname']=$request['firstname'];
        $obj['lastname']=$request['lastname'];
        $obj['profileimage']="ProfileImage/1693471659_defaultimage.jpg-5";
        $obj['password']=Hash::make($request['password']);
        $obj['email']=$request['email'];
        $obj['mobileno']=$request['mobileno'];
        $obj['gender']=$request['gender'];
        $obj['dob_d']=$request['dob_d'];
        $obj['dob_m']=$request['dob_m'];
        $obj['dob_y']=$request['dob_y'];
        $obj['country']=$request['country'];
        $obj['state']=$request['state'];
        $obj['city']=$request['city'];
        $obj['area']=$request['area'];
        $obj['pincode']=$request['pincode'];
        
		$obj['iscustomer']=1;

        $user = User::create($obj);
		
		// Otp verification code
		$otp = mt_rand(100000,999999);
		Log::info("otp is :".$otp);
		
		$message="https://2factor.in/API/V1/a0d8809d-2705-11ee-addf-0200cd936042/SMS/".$request['mobileno']."/".$otp."/LZYOTP";

		Log::info($message);
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $message,
			CURLOPT_RETURNTRANSFER =>true,
			CURLOPT_POST => true
		));
	
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
	
		$output = curl_exec($ch);
	
		if(curl_errno($ch)){
			echo 'error :'. curl_error($ch);
		}
		curl_close($ch);

		$user['tempotp']=$otp;
		$user['otpcount']=$user['otpcount']+1;
		$user->update();

		// generatedid
		$generateservice=new GeneratedIdService;
		$generateservice->generateduser($user);

		// Address
		$addressservice=new AddressService();
		$addressservice->addressCreateUpdate($request,$user);

		$user = User::where('id',$user['id'])->first();
		$response = [
				'access_token'=>null,
				'user'=>$user,
				// 'otpsent'=>$otp,
				// 'otpresponse'=>$otpres,
				'success' => true,
				'isverified'=>false,
				'message' => 'Customer created Successfully Verify Otp',
		];
		
        return response()->json($response);
    }

	public function otpVerification(Request $request){
		$response = [
			'access_token'=>null,
			'user'=>null,
			'success' => true,
			'isverified'=>false,
			'message' => 'Otp is not verified Somrthing went Wrong',
		];
		Log::info("Enter In Otp Verifying");
		if($request['id']>0){
			Log::info('Otp is verifying');
			$user=User::where('id',$request['id'])->where('isdisabled',0)->first();
			
			if($request['otp']==$user['tempotp']){
				$user['isotpverified']=1;
				$user->update();
				$accessToken = $user->createToken('my-app-token')->plainTextToken;
				Auth::login($user);
				$response = [
						'access_token'=>$accessToken,
						'user'=>$user,
						'success' => true,
						'isverified'=>true,
						'message' => 'Customer created Successfully',
				];
			}else{
				$response = [
					'access_token'=>null,
					'user'=>$user,
					'success' => true,
					'isverified'=>false,
					'message' => 'Otp is not verified and reached the max limit of send OTP',
				];
			}
		}else{
			$response = [
				'access_token'=>null,
				'user'=>null,
				'success' => true,
				'isverified'=>false,
				'message' => 'User Not Found try again signup and login',
			];
		}
		return response()->json($response);
	}

	public function logout(Request $request){
		Auth::logout();
		return redirect('login');
	}
	
	// Login with Email , MObile and Password
	public function loginwithemailpassword(Request $request){
		$response = [
			'access_token'=>null,
			'user'=>null,
			'success' => false,
			'message' => 'Email and Password Incorrect'
		];
		$user=array();
		if($request['loginuser']!=null && $request['password']!=null){
			
			$user = User::where('email',$request['loginuser'])->orWhere('mobileno',$request['loginuser'])
				->where('isdisabled',0)->first();
			
		}else{
			$response = [
				'access_token'=>null,
				'user'=>null,
				'success' => false,
				'message' => 'Email/Mobile and Password Field is compulsory',
			];
		}
		
		if($user!=null){
			Log::info($user);			
			if(Hash::check($request['password'], $user->password)){
				if($user['isotpverified']==1){
					$accessToken = $user->createToken('my-app-token')->plainTextToken;
					Auth::login($user);
					$user = User::where('id',$user['id'])->first();
					$response = [
						'access_token'=>$accessToken,
						'user'=>$user,
						'success' => true,
						'isverified'=>true,
						'message' => 'Login Successfully',
					];
				}else{
					if($user['otpcount']<=3){
						$otp = mt_rand(100000,999999);
						Log::info("otp is :".$otp);
						$message="https://2factor.in/API/V1/a0d8809d-2705-11ee-addf-0200cd936042/SMS/".$user['mobileno']."/".$otp."/LZYOTP";
						$user['tempotp']=$otp;
						$user['otpcount']=$user['otpcount']+1;
						$user->update();

						Log::info($message);

						$ch = curl_init();
						curl_setopt_array($ch, array(
						   CURLOPT_URL => $message,
						   CURLOPT_RETURNTRANSFER =>true,
						   CURLOPT_POST => true
						));
					
					   curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
					   curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
					
					   $output = curl_exec($ch);
						// Log::info($output);
					   if(curl_errno($ch)){
						   echo 'error :'. curl_error($ch);
					   }
					   curl_close($ch);
			
						// $accessToken = $user->createToken('my-app-token')->plainTextToken;
						// Auth::login($user);
						$response = [
								'access_token'=>null,
								'user'=>$user,
								// 'otpsent'=>$otp,
								// 'otpresponse'=>$otpres,
								'success' => true,
								'isverified'=>false,
								'message' => 'Customer created Successfully Verify Otp',
						];
					}else{
						$response = [
							'access_token'=>null,
							'user'=>$user,
							'success' => true,
							'isverified'=>false,
							'message' => 'Otp is not verified and reached the max limit of send OTP',
						];
					}	
				}
			} else {
				$response = [
					'access_token'=>null,
					'user'=>null,
					'success' => false,
					'isverified'=>false,
					'message' => 'Email and Password Did not match',
				];
			}
		}
		
		return response()->json($response);
	}


	// Admin Details Login
	public function adminLogin(Request $request){
		$response = [
			'access_token'=>null,
			'user'=>null,
			'success' => false,
			'message' => ''
		];
		$user=array();
		if($request['email']!=null && $request['password']!=null){
			
			$user = User::where('email',$request['email'])
				->where('isdisabled',0)
				->where('isadmin',1)
				->where('isrole','>=',2)
				->first();
			
		}else{
			$response = [
				'access_token'=>null,
				'user'=>null,
				'success' => false,
				'message' => 'Email/Mobile and Password Field is compulsory',
			];
		}
		
		if($user!=null){
			Log::info($user);			
			if(Hash::check($request['password'], $user->password)){
				$accessToken = $user->createToken('my-app-token')->plainTextToken;
				Auth::login($user);
				$user = User::where('id',$user['id'])->first();
				$response = [
					'access_token'=>$accessToken,
					'user'=>$user,
					'success' => true,
					'isverified'=>true,
					'message' => 'Login Successfully',
				];
			}else{
				$response = [
					'access_token'=>null,
					'user'=>null,
					'success' => false,
					'isverified'=>false,
					'message' => 'Login attempt Fail',
				];
			}
		}
		return response()->json($response);
	}

	public function forgetPassword(Request $request){
		$response=[
			'status'=>false,
			'isverified'=>false
		];

		$user=User::where('mobileno',$request['mobile'])->first();
		if($user !=null){
			if($user['otpcount']<=3){
				$otp = mt_rand(100000,999999);
				Log::info("otp is :".$otp);
				$message="https://2factor.in/API/V1/a0d8809d-2705-11ee-addf-0200cd936042/SMS/".$user['mobileno']."/".$otp."/LZYOTP";
				$user['tempotp']=$otp;
				$user['otpcount']=$user['otpcount']+1;
				$user->update();

				Log::info($message);

				$ch = curl_init();
				curl_setopt_array($ch, array(
					CURLOPT_URL => $message,
					CURLOPT_RETURNTRANSFER =>true,
					CURLOPT_POST => true
				));
			
				curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
			
				$output = curl_exec($ch);
			
				if(curl_errno($ch)){
					echo 'error :'. curl_error($ch);
				}
				curl_close($ch);

				// $accessToken = $user->createToken('my-app-token')->plainTextToken;
				// Auth::login($user);
				$response = [
						'access_token'=>null,
						'user'=>$user,
						// 'otpsent'=>$otp,
						// 'otpresponse'=>$otpres,
						'success' => true,
						'isverified'=>false,
						'message' => 'Customer Found Successfully Verify Otp',
				];
			}else{
				$response = [
					'access_token'=>null,
					'user'=>$user,
					'success' => false,
					'isverified'=>false,
					'message' => 'Otp reached the max limit of send OTP',
				];
			}	
		}else{
			$response = [
				'access_token'=>null,
				'user'=>$user,
				'success' => false,
				'isverified'=>false,
				'message' => 'User Not Exist, Check the number',
			];
		}	
		return response()->json($response);
	}
}
