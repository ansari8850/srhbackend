<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_app_id',
        'holder_name',
        'banks_name',
        'branch_name',
        'account_no',
        're_enter_account_no',
        'ifsc_code',
        'is_deleted',
    ];
}
