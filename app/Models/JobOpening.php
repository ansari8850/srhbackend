<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\Designation;
use \App\Models\Department;

class JobOpening extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'job_title','department','designation','job_location',
        'job_status','no_of_position','employee_type','experience',
        'skills','description'
    ];
 
    public function department(){
        return $this->belongsTo(Department::class, 'department');
    }

    public function designation(){
        return $this->belongsTo(Designation::class, 'designation');
    }

    protected static function boot()
    {
        parent::boot();

        JobOpening::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
