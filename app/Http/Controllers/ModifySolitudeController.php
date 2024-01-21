<?php

namespace App\Http\Controllers;

use App\Models\ModifySolicitude;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ModifySolitudeController extends Controller
{
    public function allSolicitudeModify()
    {
        $data = ModifySolicitude::all();
        
        return response()->json($data, 200);
    }

    public function searchSolicitude($id_solicitude)
    {
        $data = ModifySolicitude::findOrFail($id_solicitude);
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function loadSolicitudeData(Request $request, $id_request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'request_date' => 'required',
            'id_status' => 'required',
            'rut_employee' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $sol = solicitudes::where([
            'id' => $id_request
        ])->get();

        if (!$sol->count()) {
            return response()->json(['error' => 'Solicitude does not exists in DB'], 400);
        }

        $mod_sol = ModifySolicitude::create([
            'id_prevention_type_detail' => $request->get('id_prevention_type_detail'),
            'assurance' => $request->get('assurance'),
            'id_location' => $request->get('id_location'),
            'way_number' => $request->get('way_number'),
            'request_date' => $request->get('request_date'),
            'id_status' => $request->get('id_status'),
            'rut_employee' => $request->get('rut_employee'),
            'appove' => false,
            'id_request' => $id_request
        ]);

        return response()->json($mod_sol, 200);
    }

    public function appoveSolChanges($id_solicitude, $rut)
    {
        $record = ModifySolicitude::where([
            'id_solicitude' => $id_solicitude
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifySolicitude::query()->where('id_solicitude', $id_solicitude)->update(['approve' => true]);
        
        return response()->json([
            'response' => 'Changes approved',
            'appoved_by' => $this->DataModifer($rut)
        ]);
    }

    public function rejectSolChanges($id_solicitude, $rut)
    {
        $record = ModifySolicitude::where([
            'id_solicitude' => $id_solicitude
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifySolicitude::query()->where('id_solicitude', $id_solicitude)->update(['approve' => false]);
        
        return response()->json([
            'response' => 'Changes approved',
            'rejected_by' => $this->DataModifer($rut)
        ]);
    }

    public function updateSolData(Request $request, $id_solicitude)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'request_date' => 'required',
            'id_status' => 'required',
            'rut_employee' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = ModifySolicitude::where([
            'id_solicitude' => $id_solicitude
        ])->get();

        if (!$data->count()) {
            return response()->json(['error' => 'This modify is not exists'], 400);
        }

        $gate = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        }

        $id_request = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('id_request');

        try {
            $update = DB::update(
                "UPDATE consultants.solicitudes
                SET
                id_prevention_type_detail = $request->id_prevention_type_detail,
                assurance = '$request->assurance',
                id_location = $request->id_location,
                way_number = $request->way_number,
                request_date = '$request->request_date',
                id_status = $request->id_status
                WHERE id = $id_request
                ");  
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Fail to update solicitude data'], 400);
        } 

        return response()->json([
            'message' => 'Record '.$id_solicitude.' updated successfull',
            'approve_by' => $this->DataModifer($request->rut_employee)
        ]);
    }

    public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    }
}
