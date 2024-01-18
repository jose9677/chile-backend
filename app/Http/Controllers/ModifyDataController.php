<?php

namespace App\Http\Controllers;

use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModifyDataController extends Controller
{
    public function allData($id)
    {
        $sol = solicitudes::where([
            'id' => $id
        ]);

        if (!$sol->count()) {
            return response()->json(['error' => 'Solicitude does not exists in DB'], 400);
        }

        $rut = DB::table('solicitudes')->where('id', $id)->value('rut_user');
        $user_data = $this->DataUser($rut);

        $data_sol = DB::select(
            "SELECT 
            a.id as id_solicitude,
            a.request_date,
            a.id_status,
            a.id_prevention_type_detail,
            c.id as prevention_type,
            a.assurance,
            a.id_location,
            a.way_number
            FROM consultants.solicitudes as a 
            JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
            JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
            WHERE a.id = $id"
        );

        $data_doc = $this->DataDocument($id);
        $aditional_data = $this->AditionalData($id);

        return response()->json(compact('user_data', 'data_sol', 'data_doc', 'aditional_data'));
    }

    public function DataUser($rut)
    {
        $data = DB::table('users')->select(
        'rut', 'names', 'lastname', 'id_sex',
        'f_nac', 'email', 'landline', 'mobile_phone'
        )->where('rut', $rut)->get();

        return $data;
    }

    public function DataDocument($id)
    {
        $data = DB::table('documents')->select('*')->where('id_request', $id)->get();

        return $data;
    }

    public function AditionalData($id)
     {
        $data = DB::select(
            "SELECT 
            a.rut_user,
            a.carer as cuidador,
            b.percent porcentaje_descuento,
            b.aps,
            c.description as motivo,
            d.description as dedicacion,
            e.description as remuneracion,
            b.origin as origen
            FROM consultants.solicitudes as a 
            JOIN consultants.users as b ON a.rut_user = b.rut
            JOIN consultants.motives as c USING ( id_motive )
            JOIN consultants.dedications as d USING ( id_dedication )
            JOIN consultants.remunerations as e USING ( id_remuneration ) 
            WHERE a.id = $id"
        );

        return $data;
     }
}
