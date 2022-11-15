<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class requestController extends Controller
{
    public function createRequest(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
           'id_prevention_type' => 'required',
           'corporation' => 'required',
           'secure' => 'required',
           'prevention_doc' => 'required',
           //'id_home_doc' => 'required',
           'id_ubication' => 'required',
           'number_way' => 'required',
           'id_type_property' => 'required',
           'property_number' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = requests::create([
            'id_prevention_type' => $request->get('id_prevention_type'),
            'corporation' => $request->get('corporation'),
            'secure' => $request->get('secure'),
            'prevention_doc' => $request->get('prevention_doc'),
            //'id_home_doc' =>  $request->get('id_home_doc'),
            'id_ubication' => $request->get('id_ubication'),
            'number_way' => $request->get('number_way'),
            'id_type_property' => $request->get('id_type_property'),
            'property_number' => $request->get('property_number')
        ]);
        
        return response()->json([$data], 200);
    }
}
