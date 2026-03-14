<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Trainer extends Model
{
    use HasFactory;


    protected $fillable = [
        'trainer_name','email','trainig_type','mobile_no','start_date','end_date',
        'duration','role','trainer_cost','description','status','user_id',
    ];
   
   protected static function boot()
   {
       parent::boot();

       Trainer::creating(function($model) {
           $user = Auth::user();
           if($user!=null){
               $model->enteredbyid = $user->id;
               $model->organisation_id = $user->active_organisation;
           }
       });
   }
}
