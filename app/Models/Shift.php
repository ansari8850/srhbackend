<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'assing_shift_id','shift_id','shift_name','start_time','end_time','start_date','end_date'
    ];

}
