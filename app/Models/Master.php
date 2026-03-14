<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Master extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'parent_id',
        'extra_data',
        'field_id',
        'location',
        'sub_type',
        'field_name',
        'status',
    ];

    protected $casts = [
        'extra_data' => 'array', // Automatically decode JSON data
    ];

    // Relationship for parent
    public function parent()
    {
        return $this->belongsTo(Master::class, 'parent_id');
    }


}
