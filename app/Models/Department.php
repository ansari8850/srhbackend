<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\Employee; 
use \App\Models\User; 

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name','department_head','parent_department',
    ];

    protected static function boot()
    {
        parent::boot();

        Department::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }


    public function parentDepartment()
    {
        return $this->belongsTo(Department::class, 'parent_department'); 
    }

    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'department_head');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

}
