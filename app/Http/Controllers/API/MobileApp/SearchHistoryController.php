<?php

namespace App\Http\Controllers\Api\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SearchHistory;
use Auth;

class SearchHistoryController extends Controller
{
    public function listSearchHistory(Request $request){
        $user=Auth::user();

        $search_history=SearchHistory::where('user_id',$user['id'])->get();

        $response=[
            'status'=>'success',
            'message'=>'Search history Listed successfully',
            'data'=>$search_history
        ];

        return response()->json($response);
    }

    public function clearSearchHistory(Request $request){
        $user=Auth::user();

        $search_history=SearchHistory::where('user_id',$user['id'])->delete();

        $response=[
            'status'=>'success',
            'message'=>'Search history cleared successfully',
        ];

        return response()->json($response);
    }
}
