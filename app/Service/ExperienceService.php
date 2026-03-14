<?php

namespace App\Service;
use App\Models\Experience;
use Log;
use Auth;

class ExperienceService
{
    public function experienceCreateUpdate($request,$user){

        if(isset($request['experiences'])){

            foreach ($request['experiences'] as $key => $experience) {

                $data=[
                    'company_name',
                    'industry',
                    'job_title',
                    'duration',
                    'from_date',
                    'to_date',
                    'description',
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($experience[$value]) && $experience[$value]!=null && $experience[$value]!=''){
                        $obj[$value]=$experience[$value];
                    }
                }
                
                $obj['experience_letter'] = isset($experience['experience_letter']) ? json_encode($experience['experience_letter']) : null;

                $obj['user_id']=$user['id'];
        
                $user_experience=Experience::create($obj);

            }
        
            return true;
        }
        
    }
}