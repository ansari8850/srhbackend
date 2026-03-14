<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Employee;
use App\Models\ShiftMaster;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id','date','punch_in','punch_out','shift_id','overtime','total_hours_worked',
        'status','reason','shift_name','user_name'
    ];
    
   
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }
    
    public function shiftMaster(){
        return $this->belongsTo(ShiftMaster::class, 'shift_id');
    }


    protected static function boot()
    {
        parent::boot();

        Attendance::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
