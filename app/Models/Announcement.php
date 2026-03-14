<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject','expiry','attachment',
        'notify_all','notify_any_others','description',
    ];

    
    protected static function boot()
    {
        parent::boot();

        Announcement::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
