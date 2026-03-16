<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MobileAppUser;
use App\Models\Master;

class Post extends Model
{
    use HasFactory;
   
    protected $fillable = [
        'user_id',
        'user_name',
        'field_id',
        'post_type',
        'field_name',
        'title',
        'location',
        'date',
        'description',
        'rejected_reason',
        'thumbnail',
        'auto_delete_date',
        'status',
        'is_disabled',
        'post_type_id',
    ];

    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(MobileAppUser::class, 'user_id', 'id');
    }

    public function postType()
    {
        return $this->belongsTo(Master::class, 'post_type_id', 'id');
    }
}
