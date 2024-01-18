<?php

namespace App\Http\Controllers;

use App\Models\ModifySolicitude;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ModifySolitudeController extends Controller
{
    public function loadSolicitudeData(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'rut_user' => 'required',
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'id_property_type' => 'required',
            'property_number' => 'required',
            'terms_condition' => 'required',
            'request_date' => 'required',
            'id_status' => 'required',
            'applicant_at_home' => 'required',
            'prevention_is_valid' => 'required',
            'location_is_valid' => 'required',
            'comments' => 'required',
            'is_active' => 'required',
            'carer' => 'required',
            'motive' => 'required',
            'dedication' => 'required',
            'remuneration' => 'required',
            'admission_date' => 'required'
        ]);

        $sol = solicitudes::where([
            'rut_user' => $request['rut_user']
        ])->get();

        if (!$sol->count()) {
            return response()->json(['error' => 'Solicitude does not exists in DB'], 400);
        }

        $mod_sol = ModifySolicitude::create([
                
        ]);
    }
}
