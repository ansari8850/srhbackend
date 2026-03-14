<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\Master;
use Validator;
use Auth;

class MasterController extends Controller
{
    
    public function masterCreateUpdate(Request $request){

        $auth=Auth::user();

        $validator=validator($request->all(),[
            'type'=>'required', // Master type (e.g., field, post, department)
            'name'=>'required', // Name of the master
        ]);

        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];

        } else {

            try{

                $data=[
                    'type',
                    'name',
                    'parent_id',
                    'extra_data',
                    'field_id',
                    'location',
                    'sub_type',
                    'field_name',
                    'status',
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($request[$value]) && $request[$value]!=null && $request[$value]!=''){
                        $obj[$value]=$request[$value];
                    }
                }

                if($request->id>0){

                    try{
                        $master=Master::findOrFail($request['id']);
                        $master->update($obj);

                    } catch (\Exception $e) {
                        return response(['error' => true, 'message' => 'Invalid Id to update'], 404); 
                    }
                }else{

                    $master=Master::create($obj);
                }

                return response()->json([
                    'status'=>200,
                    'message'=> $request->id>0 ? 'Master update successfully':'Master created successfully',
                    'master'=>$master
                ],200);
                
            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }


    public function getMaster(Request $request){

        $user=Auth::user();

        if($user!=null){

            $masterList=Master::where('is_disabled',0);

            $masterList=$this->fetchuserbyquery($masterList,$request);
            $count=$masterList->count();
            // $sortOrder = ($request['sortOrder'] == 1) ? 'desc' : 'asc'; 
            // $sortBy = $request['sortBy'] ?? 'id';

            // $masterList = $masterList->orderby($sortBy,$sortOrder)
            $masterList = $masterList->orderby("id","desc")
                ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
                ->get();

            $response=[
                'masterList'=>$masterList,
                'count'=>$count,
                'message'=>'Successful',
            ];

        } else {
            $response=[
                'masterList'=>$masterList,
                'count'=>$count,
                'message'=>'Invalid User'
            ];
        }

        return response()->json($response);
    }
    
    // Filter Users
    public function fetchuserbyquery($masterList,$request){

        if(isset($request['search']) && $request['search']!='') {
            $keyword = "%".$request['search']."%";
            $masterList = $masterList->whereRaw(" (type like ? or name like ?) ", 
            array($keyword , $keyword));
        }

        // get custom_date data by transaction_date
        if ($request['type']!='' && isset($request['type'])) {
            $type = $request->type;
            $masterList = $masterList->where('type', $type);
        }

        if ($request['status']!='' && isset($request['status'])) {
            $status = $request->status;
            $masterList = $masterList->where('status', $status);
        }
        // get custom_date data by transaction_date
        if ($request['location']!='' && isset($request['location'])) {
            $location = $request->location;
            $masterList = $masterList->where('location', $location);
        }
        // get custom_date data by transaction_date
        if ($request['sub_type']!='' && isset($request['sub_type'])) {
            $sub_type = $request->sub_type;
            $masterList = $masterList->where('sub_type', $sub_type);
        }
        // get custom_date data by transaction_date
        if ($request['name']!='' && isset($request['name'])) {
            $name = $request->name;
            $masterList = $masterList->where('name', $name);
        }
        // get custom_date data by transaction_date
        if ($request['field_name']!='' && isset($request['field_name'])) {
            $field_name = $request->field_name;
            $masterList = $masterList->where('field_name', $field_name);
        }
        // get custom_date data by transaction_date
        if ($request['field_id']!='' && isset($request['field_id'])) {
            $field_id = $request->field_id;
            $masterList = $masterList->where('field_id', $field_id);
        }

        // get custom_date data by transaction_date
        if ($request['custom_date']!='' && isset($request['custom_date'])) {
            $customDate = $request->custom_date;
            $masterList = $masterList->whereDate('created_at', $customDate);
        }

        // get fromDate toDate data by created_at
        if ($request['fromDate']!='' && isset($request['toDate'])) {
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
            $toDate .= ($fromDate === $toDate) ? ' 23:59:59' : ' 23:59:59';
        
            // 'fromDate' and 'toDate'
            $masterList = $masterList->whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate);
        }

        return $masterList;
    }


    public function deleteMaster(Request $request){

        $auth=Auth::user();
        
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
                $master =  Master::where('id',$request->input('id'))->first();

                $master['is_disabled']='1';
                $master->update();

                $response=[
                    'success'=>true,
                    'message'=>'Master Deleted successfully.'
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
                
                $update = Master::where('id',$request->input('id'))->first();
                $update['status']=$request['status'];
                $update->update();

                $response=[
                    'success'=>true,
                    'message'=>'Status Updated successfully.',
                    'result'=>$update
                ];

                return response()->json($response);

            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }

    public function post_type_lists(Request $request){
        $user=Auth::user();

        if($user!=null){

            $masterList=Master::where('type','post')->where('is_disabled',0)
                ->where('status','Active')
                ->select('name')
                ->groupBy('name')
                ->get();

            
            $response=[
                'post_type_list'=>$masterList,
                'message'=>'Successful Post Type List',
            ];

        } else {
            $response=[
                'message'=>'Invalid User'
            ];
        }

        return response()->json($response);
    }

}
