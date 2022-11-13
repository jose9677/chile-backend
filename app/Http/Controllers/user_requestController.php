<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\user_request;
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
            'id_request' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new user_request();

        $data->rut = $request->rut;
        $data->id_request = $request->id_request;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Request successfull'], 200);
    }
}
