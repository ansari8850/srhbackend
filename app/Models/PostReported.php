<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReported extends Model
{
    use HasFactory;

       
    protected $fillable = [
        'user_id',
        'user_name',
        'post_id',
        'post_title',
        'reason',
        'date',
        'status',
    ];

    protected $table = 'post_reporteds';

    public function user()
    {
        return $this->belongsTo(MobileAppUser::class, 'user_id', 'id');
    }
}
