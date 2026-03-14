<?php



namespace App\Service;

use App\Models\BankDetails;

use Log;

use Auth;

class BankService

{
    public function bankServiceCreateUpdate($request,$user){

        if(isset($request['bank_details'])){

            foreach ($request['bank_details'] as $key => $bank) {
                $data=[

                    'holder_name','banks_name','account_no', 're_enter_account_no', 'ifsc_code','branch_name'

                ];

                foreach ($data as $key => $value) {

                    if(isset($bank[$value]) && $bank[$value]!=null && $bank[$value]!=''){

                        $obj[$value]=$bank[$value];

                    }

                }

                $obj['user_app_id']=$user['id'];
    
                $user_bank=BankDetails::create($obj);

            }

            return true;

        }

    }

}