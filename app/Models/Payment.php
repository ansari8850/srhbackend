<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(MobileAppUser::class,'user_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id');
    }
}
