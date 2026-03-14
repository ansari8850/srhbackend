<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\User;
use \App\Models\Experience;
use \App\Models\Education;
use \App\Models\Contact;
use \App\Models\Document;
use \App\Models\Employee;
use \App\Models\Designation;
use \App\Models\Department; 
use \App\Models\EmployeeHealth; 
use Auth;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'mobile_no',
        'date_of_birth',
        'age',
        'marital',
        'gender',
        'joining_date',
        'designation_id',
        'department_id',
        'reporting_manager_id',
        'date_of_exit',
        'experience',
        'employment_type',
        'employee_status',
        'source_of_hire',
        'referrer_id',
        'image',
        'role_id',
        'health_status',
    ];


    protected static function boot()
    {
        parent::boot();

        Employee::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
    

    public function contacts(){
        return $this->hasMany(Contact::class, 'user_id', 'user_id');
    }
    
    public function experiences(){
        return $this->hasMany(Experience::class, 'user_id', 'user_id');
    }

    public function educations(){
        return $this->hasMany(Education::class, 'user_id', 'user_id');
    }

    public function documents(){
        return $this->hasMany(Document::class, 'user_id', 'user_id');
    }

    public function employeeHealth(){
        return $this->belongsTo(EmployeeHealth::class, 'user_id', 'user_id');
    }

    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation(){
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function reportingManager(){
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }
}
