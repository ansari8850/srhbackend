<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use \App\Models\Experience;
use \App\Models\Education;
use \App\Models\Contact;
use \App\Models\Document;
use \App\Models\Employee;
use \App\Models\Designation;
use \App\Models\Department; 
use Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'salutation',
        'login_type',
        'name',
        'email',
        'mobile_no',
        'firebase_uid',
        'work_phone',
        'primary_organisation',
        'active_organisation',
        'is_disabled',
        'c_password',
        'password',
        'organisation_id',
        'is_email_verified',
        'email_token',
        'enteredbyid',
        'is_vendor',
        'is_customer',
        'is_employee',
        'is_agent',
        'gender',
        'shift_id',
        'customer_type',
        'company_name',
        'display_name',
        'pan_no',
        'payment_terms',
        'gst_no',
        'place_of_supply',
        'tax_preference',
        'website',
        'currency',
        'remarks',
        'custom_fields',
        'active',
        'balance',
        'registration_type',
        'upload_documents',
        'opening_balance',
        'department',
        'designation',
        'customer_note',
        'otp',
        'otp_verify',
        'role_id',
        'subscription_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    protected static function boot()
    {
        parent::boot();

        User::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }


}
