<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_type',
        'street_1',
        'street_2',
        'zip_code',
        'city_id',
        'state_id',
        'country_id',
        'personal_contact_no',
        'emergency_contact_no',
        'personal_email_id',
        'is_present_address',
    ];

    public function country(){
        return $this->belongsTo('App\Models\Country','country_id');
    }

    public function state(){
        return $this->belongsTo('App\Models\State','state_id');
    }

    public function city(){
        return $this->belongsTo('App\Models\City','city_id');
    }
}
