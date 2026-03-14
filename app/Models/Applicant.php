<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Applicant extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'job_opening_id',
        'job_opening_name',
        'resume',
        'cover_letter',
        'country_id',
        'state_id',
        'city_id',
        'zip_code',
        'source',
        'referred_by',
        'expected_salary',
        'availability_date',
        'status',
    ];


    public function country(){
        return $this->belongsTo('App\Models\Country','country_id');
    }

    public function state(){
        return $this->belongsTo('App\Models\State','state_id');
    }

    public function city(){
        return $this->belongsTo('App\Models\City','city_id');
    }

    
    protected static function boot()
    {
        parent::boot();

        Applicant::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
