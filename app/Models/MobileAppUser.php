<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\MobileAppUser as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use App\Models\Followers;
use App\Models\Post;
use Auth;

class MobileAppUser extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'c_password',
        'firebase_uid'
    ];
    
    // Users that follow this user
    public function agent()
    {
        return $this->belongsTo(MobileAppUser::class, 'agent_id')->where('login_type','Agent');
    }
    
    public function followers()
    {
        return $this->hasMany(Followers::class, 'user_id')->with('follower');
    }
    
    public function following()
    {
        return $this->hasMany(Followers::class, 'follower_id')->with('user');
    }

    public function bank_details(){
        return $this->hasMany(BankDetails::class,'user_app_id');
    }
    
    public function country(){
        return $this->belongsTo('App\Models\Country','country_id');
    }

    public function state(){
        return $this->belongsTo('App\Models\State','state_id');
    }

    public function city(){
        return $this->belongsTo('App\Models\City','city_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'user_id');
    }
}
