<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_name',
        'document_id',
        'attachment_1',
        'attachment_2',
    ];

    protected static function boot()
    {
        parent::boot();

        Document::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
