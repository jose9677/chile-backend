<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\solicitudes;
use App\Models\User;
use App\Models\users_requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class user_requestController extends Controller
{
    public function add_request(Request $request)
    {
        /*Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'id_request' => 'required',
            'employee_rut' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }*/

        users_requests::create([
            'rut' => User::select("rut")->latest()->first(),
            'id_request' => solicitudes::max('id'),
            'employee_rut' => User::select('supervisor_rut')->orderBy('created_at', 'desc')->first()
        ]);
        return response()->json(['message' => 'Request successfull'], 200);
    }
}
