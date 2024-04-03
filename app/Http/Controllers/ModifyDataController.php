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
        //$origin

        $user_data = $this->DataUser($rut);

        $data_sol = DB::select(
            "SELECT 
            a.id as id_solicitude,
            a.created_at as request_date,
            a.id_status,
            a.id_prevention_type_detail,
            c.id as prevention_type,
            a.assurance,
            a.id_location,
            a.way_number,
            a.id_property_type,
            a.property_number,
            --d.origin,
            a.reg_type,
            a.dir_wots,
            a.latitude,
            a.longitude,
            a.id_mod_status,
            a.id_status_emp
            FROM consultants.solicitudes as a 
            JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
            JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
            JOIN consultants.users as d ON a.rut_user = d.rut 
            WHERE a.id = $id"
        );

        $data_doc = $this->DataDocument($id);
        $aditional_data = $this->AditionalData($id);

        return response()->json(compact('user_data', 'data_sol', 'data_doc', 'aditional_data'));
    }

    public function DataUser($rut)
    {
        $data = DB::table('users')->select(
        'rut', 
        'names', 
        'lastname',
        'id_sex',
        'f_nac', 
        'email', 
        'landline', 
        'mobile_phone',
        'id_mod_status'
        )->where('rut', $rut)->get();

        return $data;
    }

    public function DataDocument($id)
    {
        //$data = DB::table('documents')->select('*')->where('id_request', $id)->where('active' , true)->get();

        $data = DB::select(
            "SELECT 
            id as id_document,
            id_request,
            id_document_type,
            document_path,
            document_ext,
            active,
            'http://54.189.74.47/storage/posts/'||document_path as doc_srv_path,
            id_mod_status
            FROM consultants.documents
            WHERE id_request = $id AND  active = true
            ");
            
        return $data;
    }

    public function AditionalData($id)
     {
        $data = DB::select(
            "SELECT 
            a.rut_user,
            a.carer as cuidador,
            a.comments,
            b.percent porcentaje_descuento,
            b.aps,
            a.id_motive,
            a.id_dedication,
            a.id_remuneration,
            a.origin as origen,
            a.id_mod_status as sol_mod_status,
            b.id_mod_status as user_mod_status
            FROM consultants.solicitudes as a 
            JOIN consultants.users as b ON a.rut_user = b.rut
            WHERE a.id = $id"
        );

        return $data;
     }
}
