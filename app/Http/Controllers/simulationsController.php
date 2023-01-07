<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\simulations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class simulationsController extends Controller
{
    public function detailsSimulation()
    {
        $data = simulations::all();
        return response()->json($data);
    }

    public function searchSimulation($id)
    {
        $data = simulations::findOrFail($id);
        $dataFound = $data;

        return response()->json($dataFound);
    }

    //This method get data about the user and his simulation.
    public function Users_Simulations($rut)
    {
        $query = DB::select("SELECT 
        a.id as id_simulation,
        a.created_at as emition_date,
        a.expiration_date,
        a.id_status,
        d.status,
        b.id as id_request,
        b.rut_user,
        c.names,
        c.lastname
        FROM consultants.simulations as a
        JOIN consultants.solicitudes as b ON a.id_request = b.id
        JOIN consultants.users as c ON b.rut_user = c.rut
        JOIN consultants.status as d ON a.id_status = d.id
        WHERE b.rut_user = '$rut'");

        return response()->json($query, 200);
    }

    public function addSimulation(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_request' => 'required',
            'id_status' => 'required',
            'calc_secure_comp' => 'required',
            'calc_cobert_prevention' => 'required',
            'expiration_date' => 'required',
            'amount_payable' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new simulations();
        $data->id_request = $request->id_request;
        $data->id_status = $request->id_status;
        $data->calc_secure_comp = $request->calc_secure_comp;
        $data->calc_cobert_prevention = $request->calc_cobert_prevention;
        $data->expiration_date = $request->expiration_date;
        $data->amount_payable = $request->amount_payable;
        
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Simulation created successfull'], 200);
    }

    public function updateSimulation(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_request' => 'required',
            'id_status' => 'required',
            'calc_secure_comp' => 'required',
            'calc_cobert_prevention' => 'required',
            'expiration_date' => 'required',
            'amount_payable' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new simulations();
        $data = simulations::findOrFail($id);

        $data->id_request = $request->id_request;
        $data->id_status = $request->id_status;
        $data->calc_secure_comp = $request->calc_secure_comp;
        $data->calc_cobert_prevention = $request->calc_cobert_prevention;
        $data->expiration_date = $request->expiration_date;
        $data->amount_payable = $request->amount_payable;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Update Failed'], 400);
        }

        return response()->json([
            'message' => 'Simulation Updated successfull',
            'data_simulation' => $data->id
        ], 200);
    }

    public function deleteSimulation($id)
    {
        $data = simulations::findOrFail($id);
        
        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Simulation does not exists'], 400);
        }

        return response()->json(['message' => 'Simulation '.$id.' was deleted successfull'], 200);
    }

    //SINCE THIS POINT WE START TO WORK WIHT COPAGO CALCAS...
    public function calc()
    {   
        
    }
}
