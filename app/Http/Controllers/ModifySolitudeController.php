<?php

namespace App\Http\Controllers;

use App\Models\ModifySolicitude;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    public function loadSolicitudeData(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'request_date' => 'required',
            'id_status' => 'required',
            'rut_employee' => 'required'
        ]);

        $sol = solicitudes::where([
            'id' => $request['id']
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
            'rut_employee' => $request->get('rut_employee')
        ]);

        return response()->json($mod_sol, 200);
    }
}
