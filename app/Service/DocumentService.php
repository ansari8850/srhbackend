<?php

namespace App\Service;
use App\Models\Document;
use Log;
use Auth;

class DocumentService
{
    public function documentCreateUpdate($request,$user){

        if(isset($request['documents'])){

            foreach ($request['documents'] as $key => $document) {

                $data=[
                    'document_name',
                    'document_id',
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($document[$value]) && $document[$value]!=null && $document[$value]!=''){
                        $obj[$value]=$document[$value];
                    }
                }
                
                $obj['attachment_1'] = isset($document['attachment_1']) ? json_encode($document['attachment_1']) : null;
                $obj['attachment_2'] = isset($document['attachment_2']) ? json_encode($document['attachment_2']) : null;

                $obj['user_id']=$user['id'];
        
                $user_document=Document::create($obj);

            }
        
            return true;
        }
        
    }
}