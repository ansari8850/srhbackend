<?php

namespace App\Service;
use App\Models\Contact;
use Log;
use Auth;

class ContactService
{
    public function contactCreateUpdate($request,$user){

        if(isset($request['contacts'])){

            foreach ($request['contacts'] as $key => $contact) {

                $data=[
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
                
                foreach ($data as $key => $value) {
                    if(isset($contact[$value]) && $contact[$value]!=null && $contact[$value]!=''){
                        $obj[$value]=$contact[$value];
                    }
                }
                
                $obj['user_id']=$user['id'];
        
                $user_contact=Contact::create($obj);

            }
        
            return true;
        }
        
    }
}