<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    public function createSubscriptionPlan(Request $request)
    {
        $validator=validator($request->all(),[
            'name'=>'required',
            'price'=>'required',
            'duration'=>'required',     //In Months
        ]);

        if ($validator->fails()) { 

            return [
                'success' => false, 
                'message' => $validator->errors()->first()
            ];

        } else {

            if($request['id']>0){

                try{

                    $subscriptionPlan = SubscriptionPlan::findOrFail($request['id']);
                    $subscriptionPlan->name = $request->name;
                    $subscriptionPlan->description = $request->description;
                    $subscriptionPlan->price = $request->price;
                    $subscriptionPlan->duration = $request->duration;   //in months

                    $subscriptionPlan->save();
                  
                    $response= [
                        'success' => true,
                        'message' => 'Subscription plan updated successfully.'
                    ];

                    return response()->json($response);
                    
                }
                catch(\Exception $e){
                    return [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            }
            else{

                if(SubscriptionPlan::where('name', $request->name)->exists()) {
                    $response= [
                        'success' => false,
                        'message' => 'Subscription plan already exists.'
                    ];

                    return response()->json($response);
                }
                else{
                    $subscriptionPlan = new SubscriptionPlan();
                    $subscriptionPlan->name = $request->name;
                    $subscriptionPlan->description = $request->description;
                    $subscriptionPlan->price = $request->price;
                    $subscriptionPlan->duration = $request->duration;
                    $subscriptionPlan->save();
                    $response= [
                        'success' => true,
                        'subscription'=>$subscriptionPlan,
                        'message' => 'Subscription plan created successfully.'
                    ];

                    return response()->json($response);
                }
            }
        }
    }

    public function getSubscriptionPlans()
    {
        $subscriptionPlans = SubscriptionPlan::where('rolledback',0)
            ->get();

        $response= [
            'success' => true,
            'message' => 'Subscription plans retrieved successfully',
            'subscriptionPlans' => $subscriptionPlans
        ];

        return response()->json($response);
    }

    public function getSubscriptionPlanById(Request $request)
    {

        $validator=validator($request->all(),[
            'plan_id'=>'required',    
        ]);

        if ($validator->fails()) { 

            return [
                'success' => false, 
                'message' => $validator->errors()->first()
            ];

        } else {
            $subscriptionPlan = SubscriptionPlan::find($request->plan_id);

            if (!$subscriptionPlan) {
                return response()->json(['message' => 'Subscription plan not found'], 404);
            }

            $response= [
                'success' => true,
                'message' => 'Subscription plan retrieved successfully',
                'subscriptionPlan' => $subscriptionPlan
            ];
            return response()->json($response);
        }
    }

}
