<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\MobileAppUser;
use \App\Models\Followers;
use Validator;
use Auth;
use Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Service\FirebaseService;
use Log;

class UserController extends Controller
{
    
    public function mobileAppUserCreateUpdate(Request $request){

        $validator=validator($request->all(),[
            'user_type'=>'required',
            'field'=>'required',
        ]);

        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];

        } else {

            try{

                $data=[
                    'field',
                    'field_name',
                    'agent_id',
                    'sub_field',
                    'name',
                    'last_name',
                    'email',
                    'mobile_no',
                    'emr_mobile_no',
                    // 'photo',
                    'education',
                    'department',
                    'address',
                    'attachments',
                    'remark',
                    'user_type',
                    'user_type_id',
                    'country_id',
                    'state_id',
                    'city_id',
                    'street_1',
                    'street_2',
                    'zip_code',
                    'company_name',
                    'contact_person',
                    'work_email',
                    'bio',
                    'contact_person_name',
                    'work_phone',
                    'business_leagal_name',
                    'display_name',
                    'tax_preference',
                    'gst_no',
                    'pan_no',
                    'website',
                    'registration_type',
                    'status',
                    'date',
                    'password',
                    'firebase_uid',
                    'device_token',
                    'fcm_token',
                    'nominee',
                    'location'
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($request[$value]) && $request[$value]!=null && $request[$value]!=''){
                        $obj[$value]=$request[$value];
                    }
                }

                $obj['attachments'] = isset($request['attachments']) ? json_encode($request['attachments']) : null;
                $obj['photo'] = isset($request['photo']) ? json_encode($request['photo']) : null;
                $obj['login_type'] = "User";

                if($request->id>0){

                    try{
                        $user=MobileAppUser::findOrFail(intval($request['id']));
                        $user->update($obj);

                        // implement Whatsapp
                        // $response = $this->sendWhatsAppTemplate($user['name'], $user['email'], $obj['c_password']);

                    } catch (\Exception $e) {
                        return response(['error' => true, 'message' => 'Invalid Id to update'], 404); 
                    }
                }else{

                    $checkEmail=MobileAppUser::where('email',$request['email'])->where('is_disabled',0)->first();
                    $checkMobile = MobileAppUser::where('mobile_no', $request['mobile_no'])->where('is_disabled', 0)->first();

                    if($checkEmail !=null){

                        $response=[
                            'message'=>"Email id already exist",
                            'success'=>false,
                            'vendor'=>$checkEmail
                        ];

                        return response()->json($response);
                    }

                    if ($checkMobile != null) {
                        $response = [
                            'message' => "Mobile number already exists",
                            'success' => false,
                            'vendor' => $checkMobile
                        ];
                    
                        return response()->json($response);
                    }

                    $obj['c_password'] = Str::random(10);
                    $obj['password'] = \Hash::make($obj['c_password']);

                    $user=MobileAppUser::create($obj);

                    // Mail::send('email_templates.login_password', ['user' => $user], function ($message) use ($user) {
                    //     $message->to($user['email'])->subject('SR Healthcare App Login password');
                    // });

                    // implement Whatsapp
                    $response = $this->sendWhatsAppTemplate($user['name'], $user['email'], $obj['c_password'],$user['mobile_no']);
                    // print_r($response);

                }

                return response()->json([
                    'status'=>200,
                    'message'=> $request->id>0 ? 'User update successfully':'User created successfully',
                    'user'=>$user,
                    'whatsapp_response'=>$response??null
                ],200);
                
            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }

    //update fcm token
    public function updateFcmToken(Request $request)
    {
        Log::info("Updating Fcm token");
        $validator=validator($request->all(),[
            'fcm_token'=>'required'
        ]);

        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];

        } else {
            $auth=Auth::user();
            Log::info($auth);
            try{

                if($auth->id>0){
                    $user=MobileAppUser::findOrFail($auth->id);
                    $user->fcm_token = $request->fcm_token;
                    $user->save();
                    return response()->json([
                        'status'=>200,
                        'message'=>'FCM token updated successfully'
                    ],200);
                }
                else{
                    return response()->json([
                        'status'=>401,
                        'message'=>'Unauthorized'
                    ],401);
                }
            }
            catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404);
            }
        }
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


    public function getMobileAppUser(Request $request){

        $user=Auth::user();

        if($user!=null){

            $mobileAppUserList=MobileAppUser::where('is_disabled',0)->where('login_type','User')->with([
                'country:id,name',
                'state:id,name',
                'city:id,name',
                'post',
                'agent:id,name,agent_no',
            ]);

            $mobileAppUserList=$this->fetchuserbyquery($mobileAppUserList,$request);
            $count=$mobileAppUserList->count();

            $sortOrder = ($request['sortOrder'] == 1) ? 'desc' : 'asc';
            $sortBy = $request['sortBy'] ?? 'id'; 

            // $mobileAppUserList = $mobileAppUserList->orderby($sortBy,$sortOrder)
            $mobileAppUserList = $mobileAppUserList->orderby("id","desc")
                ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
                ->get();

            $response=[
                'result'=>$mobileAppUserList,
                'count'=>$count,
                'message'=>'Successful',
            ];

        } else {
            $response=[
                'result'=>$mobileAppUserList,
                'count'=>$count,
                'message'=>'Invalid User'
            ];
        }

        return response()->json($response);
    }
    
    // Filter Users
    public function fetchuserbyquery($mobileAppUserList,$request){

        if(isset($request['search']) && $request['search']!='') {
            $keyword = "%".$request['search']."%";
            $mobileAppUserList = $mobileAppUserList->whereRaw(" (
            name like ? or email like ? 
            or mobile_no like ?
            or user_type like ?
            or field like ?
            or status like ?
            or last_name like ?
            or emr_mobile_no like ?
            or registration_type like ?
            ) ", 
            array($keyword , $keyword , 
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $keyword,
        ));
        }

        // get custom_date data by transaction_date
        if ($request['field']!='' && isset($request['field'])) {
            $field = $request->field;
            $mobileAppUserList = $mobileAppUserList->where('field', $field);
        }

        // get custom_date data by transaction_date
        if ($request['user_type']!='' && isset($request['user_type'])) {
            $user_type = $request->user_type;
            $mobileAppUserList = $mobileAppUserList->where('user_type', $user_type);
        }
       
        if ($request['status']!='' && isset($request['status'])) {
            $status = $request->status;
            $mobileAppUserList = $mobileAppUserList->where('status', $status);
        }

        if ($request['field_name']!='' && isset($request['field_name'])) {
            $field_name = $request->field_name;
            $mobileAppUserList = $mobileAppUserList->where('field_name', $field_name);
        }

        // get custom_date data by transaction_date
        if ($request['custom_date']!='' && isset($request['custom_date'])) {
            $customDate = $request->custom_date;
            $mobileAppUserList = $mobileAppUserList->whereDate('created_at', $customDate);
        }

        // get fromDate toDate data by created_at
        if ($request['fromDate']!='' && isset($request['toDate'])) {
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
            $toDate .= ($fromDate === $toDate) ? ' 23:59:59' : ' 23:59:59';

            $mobileAppUserList = $mobileAppUserList->whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate);
        }

        return $mobileAppUserList;
    }

    public function detailsMobileAppUser(Request $request)
    {
        $user=Auth::user();
        try {
            // Fetch the requested user's details
            $result = MobileAppUser::where('is_disabled',0)->where('login_type','User')
                ->with([
                    // 'followers.follower', 'following.user',
                    'followers', 'following',
                    'country:id,name',
                    'state:id,name',
                    'city:id,name',
                    'agent:id,name,agent_no',
                ])->where('id', $request['id'])->first();

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $result->is_following=Followers::where('user_id', $user->id)
                        ->where('follower_id', $request->input('id'))
                        ->exists();

            // Get followers and following with counts
            $followers = $result->followers->map(function ($follower) {
                return [
                    'id' => $follower->follower->id ?? '',
                    'name' => $follower->follower->name ?? '',
                    'email' => $follower->follower->email ?? '',
                ];
            });

            $following = $result->following->map(function ($follow) {
                return [
                    'id' => $follow->user->id ?? '',
                    'name' => $follow->user->name ?? '',
                    'email' => $follow->user->email ?? '',
                ];
            });

            // $response = [
            //     'success' => true,
            //     'result' => [
            //         'user_details' => $result,
            //         'followers' => [
            //             'total_count' => $followers->count(),
            //             'data' => $followers,
            //         ],
            //         'following' => [
            //             'total_count' => $following->count(),
            //             'data' => $following,
            //         ],
            //     ],
            // ];
            $response = [
                'success' => true,
                'result' => [
                    'user_details' => $result,
                    'followers' =>$followers->count(),
                    'following' => $following->count()
                ],
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteMobileAppUser(Request $request){

        $validator=validator($request->all(),[
           'id'=>'required|integer'
        ]);

       if ($validator->fails()) { 
           return [
               'success' => false, 
               'message' => $validator->errors()->first(),
           ];

       } else {

           try{
                $result = MobileAppUser::where('id',$request->input('id'))->first();

                $result['is_disabled']=1;
                $result->update();

                $response=[
                    'success'=>true,
                    'message'=>'User Deleted successfully.'
                ];

                return response()->json($response);

            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
       }
    }


    public function updateStatus(Request $request){

        $validator=validator($request->all(),[
            'id'=>'required|integer' 
         ]);
 
        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];
 
        } else {

            try{
                
                $update = MobileAppUser::where('id',$request->input('id'))->first();
                $update['status']=$request['status'];
                $update->update();

                // Send the Device Notification
                $firebase_notify = new FirebaseService;

                $deviceToken = $update->fcm_token;

                if($deviceToken){
                    $title = 'Your Account has been '.$request['status'];
                    $body = 'Your Account has been '.$request['status'];

                    $firebase_notify->createNotification($request['id'],$title,$body);
                    $notification=$firebase_notify->sendNotification($deviceToken, $title, $body);
                }else{
                    $notification=[
                        'success'=>false,
                        'message'=>'Device Token not found'
                    ];
                }

                Log::info("Notification Account Status Response");
                Log::info($notification);

                $response=[
                    'success'=>true,
                    'notification'=>$notification,
                    'message'=>'Status Updated successfully.',
                    'result'=>$update
                ];

                return response()->json($response);

            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }


    public function follow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mobile_app_users,id',
            'follower_id'=>'required'
        ]);

        $followerId = $request->follower_id;
        $userId = $request->user_id;

        if ($followerId == $userId) {
            return response()->json(['message' => "You can't follow yourself"], 400);
        }

        // Prevent duplicate follow
        $follow = Followers::firstOrCreate([
            'user_id' => $userId,
            'follower_id' => $followerId,
        ]);

        return response()->json([
            'message' => 'Successfully followed the user',
            'data' => $follow,
        ], 200);
    }

    // Unfollow a User
    public function unfollow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mobile_app_users,id',
            'follower_id'=>'required'
        ]);

        $followerId = $request->follower_id;
        $userId = $request->user_id;

        Followers::where('user_id', $userId)
            ->where('follower_id', $followerId)
            ->delete();

        return response()->json(['message' => 'Successfully unfollowed the user']);
    }

    public function followers(Request $request, $userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }
    
        try {
            // Fetch the followers query
            $followersQuery = MobileAppUser::where('id',$userId)
                // ->followers()
                ->with('followers');
    
            // Apply search filter if provided
            if ($request->filled('search')) {
                $keyword = "%" . $request->input('search') . "%";
                $followersQuery = $followersQuery->whereHas('follower', function ($query) use ($keyword) {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('mobile_no', 'like', $keyword)
                        ->orWhere('user_type', 'like', $keyword)
                        ->orWhere('field', 'like', $keyword)
                        ->orWhere('status', 'like', $keyword)
                        ->orWhere('last_name', 'like', $keyword)
                        ->orWhere('emr_mobile_no', 'like', $keyword)
                        ->orWhere('registration_type', 'like', $keyword);
                });
            }
    
            // Count total followers
            $totalFollowers = $followersQuery->count();
            // Paginate the results
            $followers = $followersQuery->orderby("id", "desc")
                ->skip($request->input('noofrec') * ($request->input('currentpage') - 1))
                ->take($request->input('noofrec') ?? 100)
                ->get();
    
            return response()->json([
                'success' => true,
                'total_count' => $totalFollowers,
                'data' => $followers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    // Get Users Followed by a User
    public function following(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }

        try {
            $userId = $request->input('user_id');

            // Fetch the following query
            // $followingQuery = MobileAppUser::where('id',$userId)
            //     // ->following()
            //     ->with('following');

            $followingQuery=Followers::where('follower_id',$userId)
                ->with('user');

            // Apply search filter if provided
            if ($request->filled('search')) {
                $keyword = "%" . $request->input('search') . "%";
                $followingQuery = $followingQuery->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', $keyword)
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('mobile_no', 'like', $keyword)
                        ->orWhere('user_type', 'like', $keyword)
                        ->orWhere('field', 'like', $keyword)
                        ->orWhere('status', 'like', $keyword)
                        ->orWhere('last_name', 'like', $keyword)
                        ->orWhere('emr_mobile_no', 'like', $keyword)
                        ->orWhere('registration_type', 'like', $keyword);
                });
            }

            // Count total following
            $totalFollowing = $followingQuery->count();

            // Paginate the results
            $following = $followingQuery->orderby("id", "desc")
                ->skip($request->input('noofrec') * ($request->input('currentpage') - 1))
                ->take($request->input('noofrec') ?? 100)
                ->get();

            return response()->json([
                'success' => true,
                'total_count' => $totalFollowing,
                'data' => $following,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function web_followers(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }
        try{
            
            $followers=Followers::where('user_id',$request['user_id']);
            // ->with('follower');

            if($request['search']!=null){
                $followers=$followers->whereHas('user',function($q) use ($request){
                    $q->where('display_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('first_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $request['search'] . '%');
                });
            }

            $count=$followers->count();

            $followers=$followers->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
            ->get()
            ->map(function ($follower) {
                 // Get all columns of the Followers table
                // $followerData = $follower->toArray();
                $followerData = $follower->user ? $follower->user->toArray() : [];

                // Map additional fields from the related user
                $userData = [
                    'email' => $follower->user->email ?? null,
                    'name' => ($follower->user->first_name ?? '') . ' ' . ($follower->user->last_name ?? ''),
                    'display_name' => $follower->user->display_name ?? null,
                    'field_name' => $follower->user->field_name ?? null,
                    'field_id' => $follower->user->field ?? null,
                    'photo' => $follower->user->photo ?? null,
                    'user_type_id' => $follower->user->user_type_id ?? 0,
                    'user_id' => $follower->user->id ?? 0,
                ];

                // Merge the Followers table data with the related user data
                return array_merge($followerData, $userData);
            });

            return response()->json([
                'success' => true,
                'total_count' => $count,
                'followers' => $followers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function web_following(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }
        try{
            $following=Followers::where('follower_id',$request['user_id']);

            if($request['search']!=null){
                $following=$following->whereHas('follower',function($q) use ($request){
                    $q->where('display_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('first_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $request['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $request['search'] . '%');
                });
            }

            $count=$following->count();

            $following=$following->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
            ->get()
            ->map(function ($follower) {
               // Get all columns of the follower record
                $allColumns = $follower->follower ? $follower->follower->toArray() : [];

                // Map additional fields or modify existing fields if needed
                $mappedData = [
                    'email' => $follower->follower->email ?? null,
                    'name' => $follower->follower->first_name . ' ' . $follower->follower->last_name ?? null,
                    'display_name' => $follower->follower->display_name ?? null,
                    'photo' => $follower->follower->photo ?? null,
                    'field_name' => $follower->follower->field_name ?? null,
                    'field_id' => $follower->follower->field ?? null,
                    'user_type_id' => $follower->follower->user_type_id ?? 0,
                    'id' => $follower->follower->id ?? 0,
                ];

                // Merge all columns with mapped data
                return array_merge($allColumns, $mappedData);
            });

            return response()->json([
                'success' => true,
                'total_count' => $count,
                'followings' => $following,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}

