<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MobileAppUser;

class Followers extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'follower_id',
    ];

    protected $table = 'followers';

    // Define the follower relationship
    public function follower()
    {
        return $this->belongsTo(MobileAppUser::class, 'follower_id');
    }

    // Define the user relationship (the user being followed)
    public function user()
    {
        return $this->belongsTo(MobileAppUser::class, 'user_id');
    }
    

}
