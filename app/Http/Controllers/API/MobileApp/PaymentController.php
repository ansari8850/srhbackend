<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Service\FirebaseService;
use Razorpay\Api\Api;
use Carbon\Carbon;
use Log;

class PaymentController extends Controller
{
    public function verifyAndSavePayment(Request $request){

        $validator = validator($request->all(), [
            'user_id' => 'required',
            'payment_id' => 'required',
            // 'order_id' => 'required',
            'signature' => 'required',
            'amount' => 'required|numeric',
            'subscription_plan_id' => 'required',
            'currency' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Get authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            // ✅ Step 1: Verify Payment Signature
            $attributes = [
                'razorpay_payment_id' => $request->payment_id,
                'razorpay_order_id' => $request->order_id,
                'razorpay_signature' => $request->signature
            ];

            $api->utility->verifyPaymentSignature($attributes);

            Log::info("Payment Signature Verified Successfully for User id =".$user->id);

            // ✅ Step 2: Fetch Payment from Razorpay API
            $payment = $api->payment->fetch($request->payment_id);

            if ($payment->status !== 'captured') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed. Payment is not captured.'
                ], 400);
            }

            // ✅ Step 3: Save Payment Details in Database
            $savedPayment = Payment::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'order_id' => $request->order_id,
                'signature' => $request->signature,
                'subscription_plan_id' => $request->subscription_plan_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => $payment->status, // should be 'captured'
                'method' => $payment->method,
                'description' => $payment->description ?? 'Subscription Payment',
            ]);

            // GET SUBSCRIPTION PLAN
            $subscriptionPlan = SubscriptionPlan::find($request->subscription_plan_id);

            // ✅ Step 4: Create Subscription if Payment is Captured
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'payment_id' => $savedPayment->id,
                'subscription_plan_id' => $request->subscription_plan_id,
                'amount' => $request->amount,
                'start_date' => Carbon::now()->format('Y-m-d'),
                'valid_till' =>Carbon::now()->addMonths($subscriptionPlan['duration']??12)->format('Y-m-d'), // 1-year subscription
            ]);

            // ✅ Step 5: Update User's Subscription ID
            $user->subscription_id = $subscription->id;
            $user->is_subscription = 1;
            $user->save();

            // Send the Device Notification
            $firebase_notify = new FirebaseService;

            $deviceToken = $user->fcm_token;

            if($deviceToken){
                $title = 'Your subscription has been activated';
                $body = 'You has successfully subscribed to '.$subscriptionPlan['amount'].' plan';

                $firebase_notify->createNotification($user['id'],$title,$body);
                $notification=$firebase_notify->sendNotification($deviceToken, $title, $body);
            }else{
                $notification=[
                    'success'=>false,
                    'message'=>'Device Token not found'
                ];
            }

            Log::info("notification Payment Successfully for User id =".$user->id);
            Log::info($notification);

            return response()->json([
                'success' => true,
                'notification'=>$notification,
                'message' => 'Payment verified and subscription created successfully',
                'subscription' => $subscription
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }


    // List of all Payments of a User
    public function listUserPayments(Request $request){
        // Get authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $payments = Payment::with('subscriptionPlan','user');

        if(isset($request['user_id']) && $request['user_id']!=null && $request['user_id']!=''){
            $payments = $payments->where('user_id', $request['user_id']);
        }

        $count=$payments->count();
         
        // Pagination
        $payments =$payments->orderby("id","desc")
            ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
            ->get();

        // Return response
        return response()->json([
            'success' => true,
            'count'=>$count,
            'message' => 'Payments retrieved successfully',
            'payments' => $payments
        ], 200);    

    }

    // List of all Subscriptions of a User for mobile app
    public function listUserSubscriptions(Request $request)
    {
        // Get authenticated user
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get only active subscriptions where valid_till is in the future
        $subscriptions = Subscription::where('valid_till', '>=', Carbon::now()->format('Y-m-d')) // Filter valid subscriptions
            ->with('subscriptionPlan','user')
            ->where('user_id',$user->id);

        if(isset($request['user_id']) && $request['user_id']!=null && $request['user_id']!=''){
            $subscriptions = $subscriptions->where('user_id', $request['user_id']);
        }

        $count=$subscriptions->count();

        $subscriptions = $subscriptions->get();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'Valid subscriptions retrieved successfully',
            'subscriptions' => $subscriptions
        ], 200);
    }


    // List of all Subscriptions of a User for mobile app
    public function listUserSubscriptionsDashboard(Request $request)
    {
        // Get authenticated user
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get only active subscriptions where valid_till is in the future
        $subscriptions = Subscription::where('valid_till', '>=', Carbon::now()->format('Y-m-d')) // Filter valid subscriptions
            ->with('subscriptionPlan','user');
            // ->where('user_id',$user->id);

        if(isset($request['user_id']) && $request['user_id']!=null && $request['user_id']!=''){
            $subscriptions = $subscriptions->where('user_id', $request['user_id']);
        }

        $count=$subscriptions->count();

        $subscriptions = $subscriptions->get();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'Valid subscriptions retrieved successfully',
            'subscriptions' => $subscriptions
        ], 200);
    }

    
}
