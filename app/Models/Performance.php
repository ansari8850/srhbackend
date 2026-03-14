<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','user_name','technical','organisation','status','date'
    ];
   
    public function employee(){
        return $this->belongsTo(Employee::class, 'user_id','user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
    
   protected static function boot()
   {
       parent::boot();

       Performance::creating(function($model) {
           $user = Auth::user();
           if($user!=null){
               $model->enteredbyid = $user->id;
               $model->organisation_id = $user->active_organisation;
           }
       });
   }
}
