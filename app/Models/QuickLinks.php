<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class QuickLinks extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','link_name','link_url',
    ];

    protected static function boot()
    {
        parent::boot();

        QuickLinks::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
