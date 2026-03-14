<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\Designation;
use \App\Models\Department; 
use \App\Models\Employee; 

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'designation_name',
        'department_id',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        Designation::creating(function($model) {
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

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
