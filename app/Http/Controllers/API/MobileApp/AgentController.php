<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MobileAppUser;
use App\Models\BankDetails;
use App\Service\BankService;
use Auth;
use Hash;

class AgentController extends Controller
{
    // Create a new agent
    public function createAgent(Request $request){

        $validator=validator($request->all(),[
            'name'=>'required',
            'email'=>'required',
            'mobile_no'=>'required',
            // 'password'=>'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {

            $obj['name']=$request['name'];
            $obj['display_name']=$request['name'];
            $obj['role']=$request['role'];
            $obj['gender']=$request['gender'];
            $obj['date_of_birth']=$request['date_of_birth'];
            $obj['login_type']='Agent';
            $obj['email']=$request['email'];
            $obj['mobile_no']=$request['mobile_no'];
            $obj['description']=$request['description'];
            $obj['nominee']=$request['nominee'];

            if($request['password']){
                $obj['password']=Hash::make($request['password']);
                $obj['c_password']=$request['password'];
            }

            // Address details
            $obj['country_id']=$request['country_id'];
            $obj['state_id']=$request['state_id'];
            $obj['city_id']=$request['city_id'];
            $obj['street_1']=$request['street_1'];
            $obj['street_2']=$request['street_2'];
            $obj['zip_code']=$request['zip_code'];  

            // Work Details
            $obj['date']=$request['joining_date'];
            $obj['reporting_manager']=$request['reporting_manager'];
            $obj['employment_type']=$request['employment_type'];

             // Generate unique agent_no
            $obj['agent_no'] = 'AG' . time(); // Example: AG1708252332

            if($request['id']>0){
                try{
                    $user=MobileAppUser::findOrFail(intval($request['id']));
                    $user->update($obj);

                    //Deleting Existing Bank Details
                    BankDetails::where('user_app_id',$request['id'])->delete(); // delete bank details

                    //createing new Bank Details
                    $bank_service=new BankService;
                    $bank_service->bankServiceCreateUpdate($request,$user); // create bank details

                    $response=[
                        'message'=>'Agent Updated Successfully',
                        'success'=>true,
                        'user'=>$user
                    ];
                    return response()->json($response);
                }
                catch (\Exception $e) {
                    return response()->json(['error' => true, 'message' => 'Invalid Id to update'], 404);
                }
            }else{
                $checkEmail=MobileAppUser::where('email',$request['email'])->where('is_disabled',0)->first();
                $checkMobile = MobileAppUser::where('mobile_no', $request['mobile_no'])->where('is_disabled', 0)->first();
                if($checkEmail || $checkMobile){
                    return response(['error' => true, 'message' => 'Email or Mobile No already exists'], 404);
                }
                $user=MobileAppUser::create($obj);

                //createing new Bank Details
                $bank_service=new BankService;
                $bank_service->bankServiceCreateUpdate($request,$user); // create bank details

                $response=[
                    'message'=>'Agent Created Successfully',
                    'success'=>true,
                    'user'=>$user
                ];
                return response()->json($response);
            }
        }
    }

    // Get Agent List
    public function getAgents(Request $request)
    {
        $agents = MobileAppUser::where('login_type', 'Agent')->where('is_disabled',0)
            ->with('bank_details');

        $agents = $this->fetchuserbyquery($agents,$request);

        $count=$agents->count();
            
        $agents=$agents->orderby("id","desc")
            ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
            ->get();;

        $response=[
            'success' => true,
            'message' => 'Agent list retrieved successfully',
            'count'=>$count,
            'data' => $agents,
        ];

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
            or registration_type like ?
            ) ", 
            array($keyword , $keyword , 
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

        if ($request['employment_type']!='' && isset($request['employment_type'])) {
            $employment_type = $request->employment_type;
            $mobileAppUserList = $mobileAppUserList->where('employment_type', $employment_type);
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

    // Get Agent Details
    public function getAgentDetails(Request $request)
    {
        $validator = validator($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];
        }else{

        
            try {
                $agent = MobileAppUser::findOrFail($request['id']);
                $agent->load('bank_details','country:id,name',
                    'state:id,name',
                    'city:id,name');

                $response = [
                    'success' => true,
                    'message' => 'Agent details retrieved successfully',
                    'data' => $agent,
                ];
                return response()->json($response);
            }
            catch (\Exception $e) {
                return response()->json(['error' => true, 'message' => 'Invalid Id'], 404);
            }
        }
    }

}
