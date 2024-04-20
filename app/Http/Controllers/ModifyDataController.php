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
            h.prevention_detail,
            c.id as id_prevention_type,
            c.prevention_type,
            a.assurance,
            a.id_location,
            i.street,
            a.way_number,
            a.id_property_type,
            g.description as property_type,
            a.property_number,
            --a.origin as sol_origin,
            a.reg_type,
            a.dir_wots,
            a.latitude,
            a.longitude,
            a.id_mod_status,
            --a.approve,
            e.description as mod_status,
            a.id_status_emp,
            f.status as status_emp
            FROM consultants.solicitudes as a 
            JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
            JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
            JOIN consultants.users as d ON a.rut_user = d.rut 
            JOIN consultants.mod_status as e ON a.id_mod_status = e.id_mod_status
            JOIN consultants.status as f ON a.id_status_emp = f.id
            JOIN consultants.property_types as g ON a.id_property_type = g.id
            JOIN consultants.preventions_details as h ON a.id_prevention_type_detail = h.id
            JOIN consultants.locations as i ON a.id_location = i.id
            WHERE a.id = $id"
        );

        $data_doc = $this->DataDocument($id);
        $aditional_data = $this->AditionalData($id);

        return response()->json(compact('user_data', 'data_sol', 'data_doc', 'aditional_data'));
    }

    public function DataUser($rut)
    {
        $data = DB::table('users')->select(
        'users.rut', 
        'users.names', 
        'users.lastname',
        'users.id_sex',
        'sex.description as sex',
        'users.f_nac', 
        'users.email', 
        'users.landline', 
        'users.mobile_phone',
        'users.id_mod_status',
        'mod_status.description as mod_status'
        )->join('mod_status', 'mod_status.id_mod_status', '=', 'users.id_mod_status')
        ->join('sex', 'sex.id_sex', '=', 'users.id_sex')
        ->where('rut', $rut)->get();

        return $data;
    }

    public function DataDocument($id)
    {
        //$data = DB::table('documents')->select('*')->where('id_request', $id)->where('active' , true)->get();

        $data = DB::select(
            "SELECT 
            a.id as id_document,
            a.id_request,
            a.id_document_type,
            a.document_path,
            a.document_ext,
            a.active,
            'http://54.189.74.47/storage/posts/'||a.document_path as doc_srv_path,
            a.id_mod_status,
            b.description as mod_status
            FROM consultants.documents as a 
            JOIN consultants.mod_status as b ON a.id_mod_status = b.id_mod_status
            WHERE a.id_request = $id AND  a.active = true
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
            f.description as motive,
            a.id_dedication,
            g.description as dedication,
            a.id_remuneration,
            h.description as remuneration,
            a.origin as origen,
            a.id_mod_status as id_sol_mod_status,
            c.description as sol_mod_status,
            b.id_mod_status as id_user_mod_status,
            d.description as user_mod_status,
            a.id_adi_status,
            e.description as sol_adi_status
            FROM consultants.solicitudes as a 
            JOIN consultants.users as b ON a.rut_user = b.rut
            JOIN consultants.mod_status as c ON a.id_mod_status = c.id_mod_status
            JOIN consultants.mod_status as d ON b.id_mod_status = d.id_mod_status
            JOIN consultants.mod_status as e ON a.id_adi_status = e.id_mod_status
            LEFT JOIN consultants.motives as f USING (id_motive)
            LEFT JOIN consultants.dedications as g USING (id_dedication)
            LEFT JOIN consultants.remunerations as h USING (id_remuneration)  
            WHERE a.id = $id"
        );

        return $data;
     }
}
