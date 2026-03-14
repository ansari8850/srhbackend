<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_name','from_date','to_date','description',
    ];

    protected static function boot()
    {
        parent::boot();

        Holiday::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
