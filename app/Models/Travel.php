<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\Department; 
use \App\Models\Employee; 

class Travel extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id','user_name','department_id','purposeofvisit',
        'placeofvisit','expected_date_of_arrival','expected_date_of_departure',
        'expected_duration_in_days','is_billable','customer_name',
    ];

    
    protected static function boot()
    {
        parent::boot();

        Travel::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }

    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }
    
    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id');
    }
}
