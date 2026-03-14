<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class ShiftMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_name','start_time','end_time','break_time','extra_hours','status'
    ];
   
   protected static function boot()
   {
       parent::boot();

       ShiftMaster::creating(function($model) {
           $user = Auth::user();
           if($user!=null){
               $model->enteredbyid = $user->id;
               $model->organisation_id = $user->active_organisation;
           }
       });
   }
}
