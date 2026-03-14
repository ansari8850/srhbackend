<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Post;
use \App\Models\MobileAppUser;

class PostBookMark extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id'];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(MobileAppUser::class, 'user_id');
    }
}
