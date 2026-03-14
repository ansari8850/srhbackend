<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Validator;
use Auth;

class NotificationController extends Controller
{

    // List of Notifications
    public function getNotification(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }

        
        $user_id = $request->user_id;
        $notifications = Notification::where('user_id',$user_id)
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $response=[
            'success'=>true,
            'message'=>'Notification List',
            'result'=>$notifications
        ];
        return response()->json($response);
    }


    // Delete Single Notification
    public function deleteNotification(Request $request){
        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required|exists:mobile_app_users,id',
            'notification_id' => 'required|exists:notifications,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }
        // $user_id = $request->user_id;
        $notification_id = $request->notification_id;

        $notification = Notification::where('id',$notification_id)->first();
        $notification->delete();

        $response=[
            'success'=>true,
            'message'=>'Notification Deleted successfully.'
        ];

        return response()->json($response);
    }


    // Delete All Notification
    public function deleteAllNotification(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:mobile_app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first('user_id'),
            ], 400);
        }
        $user_id = $request->user_id;
        $notification = Notification::where('user_id',$user_id)->delete();
        $response=[
            'success'=>true,
            'message'=>'All Notification Deleted successfully.'
        ];
        return response()->json($response);
    }


}
