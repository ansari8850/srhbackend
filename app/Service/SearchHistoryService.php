<?php

namespace App\Service;
use App\Models\SearchHistory;
use Log;
use Auth;

class SearchHistoryService
{
    public function searchCreate($search,$user){
        $obj['search_query']=$search;
        $obj['user_id']=$user['id'];

        SearchHistory::create($obj);
    }
}