<?php

namespace App\Service;
use App\Models\Education;
use Log;
use Auth;

class EducationService
{
    public function educationCreateUpdate($request,$user){

        if(isset($request['educations'])){

            foreach ($request['educations'] as $key => $education) {

                $data=[
                    'institute_name',
                    'degree',
                    'specialization',
                    'date_of_completion',
                    'from_date',
                    'to_date',
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($education[$value]) && $education[$value]!=null && $education[$value]!=''){
                        $obj[$value]=$education[$value];
                    }
                }
                $obj['attachment'] = isset($education['attachment']) ? json_encode($education['attachment']) : null;

                $obj['user_id']=$user['id'];
        
                $user_education=Education::create($obj);

            }
        
            return true;
        }
        
    }
}