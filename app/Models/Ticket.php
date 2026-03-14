<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\User; 

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'requested_to',
        'priority',
        'subject',
        'attachment',
        'date',
        'description',
        'status',
    ];

    
    protected static function boot()
    {
        parent::boot();

        Ticket::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }

    public function employee(){
        return $this->hasMany(User::class, 'id','user_id');
    }
    
    public function requestedTo(){
        return $this->hasMany(User::class, 'id','requested_to');
    }
}

