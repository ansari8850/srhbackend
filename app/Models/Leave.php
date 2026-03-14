<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Leave extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id','name','leave_type_id','leave_type_name','from_date','to_date',
        'type_of_leave','days','resion','status'
    ];

    protected static function boot()
    {
        parent::boot();

        Leave::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
