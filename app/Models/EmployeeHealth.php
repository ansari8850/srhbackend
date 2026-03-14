<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\User; 
use \App\Models\Department; 

class EmployeeHealth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_name',
        'department_id',
        'gender',
        'mobile_no',
        'contact_name',
        'blood_group',
        'weight',
        'height',
        'allergies',
        'chronic_condition',
        'current_medications',
        'last_checkup_date',
        'next_checkup_date',
        'checkup_result',
        'covid_affected',
        'covid_status',
        'notes',
        'attachment',
    ];


    protected static function boot()
    {
        parent::boot();

        EmployeeHealth::creating(function($model) {
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
}
