<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
 
class MyFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'file_name',
        'attachment',
        'deadline_date',
        'notify_all',
        'notify_any_others',
        'department_id', //share with
        'file_type',
        'description',
    ];


    protected static function boot()
    {
        parent::boot();

        MyFiles::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
