<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Education extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'institute_name',
        'degree',
        'specialization',
        'attachment',
        'date_of_completion',
        'from_date',
        'to_date',
    ];

    protected static function boot()
    {
        parent::boot();

        Education::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
