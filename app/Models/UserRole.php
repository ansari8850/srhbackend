<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class UserRole extends Model
{
    use HasFactory;


    protected $fillable = [
        'role_name',
        'status',
        'enteredbyid',
        'organisation_id',
        'is_disabled',
    ];

    protected static function boot()
    {
        parent::boot();

        UserRole::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }

}
