<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Trainer;
use App\Models\User;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainig_type','trainer_id','user_id','start_date','end_date','duration',
        'training_cost','description','status','user_name','trainer_name'
    ];

    public function trainer(){
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }
    
    public function employe(){
        return $this->belongsTo(User::class, 'user_id');
    }
    

   protected static function boot()
   {
       parent::boot();

       Training::creating(function($model) {
           $user = Auth::user();
           if($user!=null){
               $model->enteredbyid = $user->id;
               $model->organisation_id = $user->active_organisation;
           }
       });
   }
}
