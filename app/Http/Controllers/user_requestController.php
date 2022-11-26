<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\users_requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class user_requestController extends Controller
{
    public function add_request(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'id_request' => 'required',
            'employee_rut' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new users_requests();

        $data->rut = $request->rut;
        $data->id_request = $request->id_request;
        $data->employee_rut = $request->employee_rut;
        
        
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Request successfull'], 200);
    }
}
