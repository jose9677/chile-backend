<?php

namespace App\Http\Controllers;
use App\Models\services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class servicesController extends Controller
{
    public function detailsServices()
    {
        $data = services::all();
        return response()->json($data);
    }

    public function searchServices($id)
    {
        $data = services::findOrFail($id);
        $dataFound = $data;

        return response()->json($dataFound);
    }

    public function add_services(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_category' => 'required',
            'description' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new services();
        $data->id_category = $request->id_category;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Services created successfull'], 200);
    }

    public function updateServices(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_category' => 'required',
            'description' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new services();
        $data = services::findOrFail($id);
        $data->id_category = $request->id_category;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Update Failed'], 400);
        }

        return response()->json([
            'message' => 'Services Updated successfull',
            'data_services' => $data->id 
        ], 200);
    }

    public function deleteServices($id)
    {
        $data = services::findOrFail($id);
        
        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Services does not exists'], 400);
        }

        return response()->json(['message' => 'Services deleted succesfull'], 200);
    }
}
