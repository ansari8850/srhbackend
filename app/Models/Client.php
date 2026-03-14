<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use \App\Models\Designation;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name','email','gender','company_name','designation','mobile_no',
        'company_no','client_image','website','client_address','description','status'
    ];

    protected static function boot()
    {
        parent::boot();

        Client::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }

    public function designation(){
        return $this->belongsTo(Designation::class, 'designation');
    }
}
