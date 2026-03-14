<?php

namespace App\Service;
use App\Models\Shift;
use Log;
use Auth;

class ShiftService
{
    public function shiftsCreateUpdate($request, $assingShift)
    {
        if (isset($request['shifts'])) {
            foreach ($request['shifts'] as $shift) {
                $data = [
                    'shift_id', 'shift_name', 'start_time', 'end_time','start_date','end_date'
                ];

                $obj = [];
                foreach ($data as $value) {
                    if (isset($shift[$value]) && $shift[$value] !== null && $shift[$value] !== '') {
                        $obj[$value] = $shift[$value];
                    }
                }

                $obj['assing_shift_id'] = $assingShift->id;

                if ($request->id > 0) {
                    $shift = Shift::where('assing_shift_id',$request['id'])->where('id',$shift['id'])->first();
                    if (!$shift) {
                        return response(['error' => true, 'message' => 'Invalid Id to update'], 404);
                    }
                    $shift->update($obj);
                }else{

                    // $checkShift=Shift::where('assing_shift_id',$request['id'])
                    //             ->where('start_date',$shift['start_date'])
                    //             ->where('is_disabled','0')->first();

                    // if($checkShift !=null){

                    //     $response=[
                    //         'message'=>"Shift already exist",
                    //         'success'=>false,
                    //         'vendor'=>$checkShift
                    //     ];

                    //     return response()->json($response);
                    // }

                    Shift::create($obj);
                }
                // Access object properties instead of array syntax
                
            }
            return true;
        }

        return false;
    }
}