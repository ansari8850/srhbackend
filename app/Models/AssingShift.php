<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Shift;

class AssingShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id','user_id','date','shift_id','shift_name','start_time','end_time',
        'extra_hours','status','shift'
    ];
   
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }
    
    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function shifts(){
        return $this->hasMany(Shift::class, 'assing_shift_id');
    }
    
   protected static function boot()
   {
       parent::boot();

       AssingShift::creating(function($model) {
           $user = Auth::user();
           if($user!=null){
               $model->enteredbyid = $user->id;
               $model->organisation_id = $user->active_organisation;
           }
       });
   }
}
