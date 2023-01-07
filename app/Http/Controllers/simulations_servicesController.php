<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\simulations_services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class simulations_servicesController extends Controller
{
    public function details_simulationService()
    {
        $data = simulations_services::all();
        return response()->json($data);
    }

    public function add_simulationService(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id_simulation' => 'required',
            'id_service' => 'required',
            'quantity' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new simulations_services();

        $data->id_simulation = $request->id_simulation;
        $data->id_service = $request->id_service;
        $data->quantity = $request->quantity;

        $data->save();

        return response()->json($data, 200);

    }

    //this method lend create one or more records
    public function create_simulationService(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id_simulation' => 'required',
            'id_service' => [],
            'quantity' => []
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id_service = $request->id_service;
        $quantity = $request->quantity;
        
        for ($i = 0; $i < count($id_service) ; $i++) { 
            $data = new simulations_services();

            $data->id_simulation = $request->id_simulation;
            $data->id_service = $id_service[$i];
            $data->quantity = $quantity[$i];

            $data->save();
        }

        return response()->json($data, 200);
    }
}
