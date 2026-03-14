<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class LeaveMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_type',
        'type_of_leave',
        'available_days',
        'status',
        'description',
    ];


    protected static function boot()
    {
        parent::boot();

        LeaveMaster::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
