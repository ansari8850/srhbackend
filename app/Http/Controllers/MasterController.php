<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master;
// use App\Models\MasterDescription;
use Auth;

class MasterController extends Controller
{
   
    public function listall(){
		$user=Auth::user();
		$list = Master::where('companyid',$user['companyid'])->where('type','>',0)->get();
		return response()->json($list);
	}
	
	public function getValues(Request $request){
		$user=Auth::user();
		$list = Master::where('type',$request['type'])->get();
		return response()->json($list);
	}
	
	public function createupdate(Request $request){														
		if(isset($request['id']) && $request['id']!=0 && $request['id']!=null ){
			$master = Master::where('id',$request['id'])->first();
			$keys = ['type','label','labelid','sequence','value'];
			foreach($keys as $k){
				if($request[$k]!=null && $request[$k]!="")
					$master[$k]=$request[$k];
			}
			$master->update();
			$list = Master::where('type',$request['type'])->get();
		} else {
			// $master = array();
			// $keys = ['type','label','labelid','sequence','value'];
			$master['type']=$request['type'];
			$master['label']=$request['label'];
			$master['labelid']=$request['labelid'];
			// $master['sequence']=$request['sequence'];
			$master['value']=$request['value'];
			$master = Master::create($master);
			$list = Master::where('type',$request['type'])->get();
		}
		return response()->json($list);
	}
}
