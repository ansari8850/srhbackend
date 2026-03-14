<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use Auth;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name','client_id','priority','rate','start_date','end_date',
        'project_leader','team_id','attachments','description','status','department_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class,'client_id');
    }
    
    protected static function boot()
    {
        parent::boot();

        Project::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
