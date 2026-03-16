<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class UserPermissions extends Model
{
    use HasFactory;


    protected $fillable = [
        'role_id',
        'modules',
        'organisation_id',
        'is_disabled',
    ];

    protected static function boot()
    {
        parent::boot();

        UserPermissions::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
