<?php

namespace App\Http\Controllers;

use App\Models\ModifySolicitude;
use App\Models\solicitudes;
use App\Providers\BearerTokenServiceProvider;
use Exception;
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

    public function searchSolicitude($id_request)
    {
        /* $data = DB::table('modify_solicitudes')->where('id_request', $id_request)
                    ->orderByDesc('id_solicitude')
                    ->limit(1)
                    ->get(); */

        $data = DB::select(
            "SELECT 
            a.id_solicitude,
            a.created_at as request_date,
            a.id_status,
            a.id_prevention_type_detail,
            c.id as id_prevention_type,
            c.prevention_type,
            a.assurance,
            a.id_location,
            a.way_number,
            a.id_property_type,
            a.property_number,
            --a.origin as sol_origin,
            a.reg_type,
            a.dir_wots,
            a.latitude,
            a.longitude,
            a.id_mod_status,
            a.approve,
            e.description as mod_status,
            a.id_status_emp
            FROM consultants.modify_solicitudes as a 
            JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
            JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
            JOIN consultants.users as d ON a.rut_user = d.rut 
            JOIN consultants.mod_status as e ON a.id_mod_status = e.id_mod_status
            WHERE a.id_request = $id_request AND a.id_mod_status = 1 
            ORDER BY a.id_solicitude DESC
            LIMIT 1"
        );                    
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function seacrhExtraData($id_request)
    {
        $data = DB::table('modify_solicitudes')->where('id_request', $id_request)
                    ->orderByDesc('id_solicitude')
                    ->limit(1)
                    ->get([
                        'comments',
                        'carer',
                        'id_motive',
                        'id_dedication',
                        'id_remuneration',
                        'id_mod_status'
                    ]);
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function loadSolicitudeData(Request $request, $id_request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $sol = solicitudes::where([
            'id' => $id_request
        ])->get();

        if (!$sol->count()) {
            return response()->json(['error' => 'Solicitude does not exists in DB'], 400);
        }

        $mod_status = DB::table('solicitudes')->where('id', $id_request)->value('id_mod_status');

        if ($mod_status == 1) {
            return response()->json(['error' => 'This users has a modify earring '], 400);
        }

        $rut_user = DB::table('solicitudes')->where('id', $id_request)->value('rut_user');

        if ($request->reg_type == 'WOTS') {
            $mod_sol = ModifySolicitude::create([
                'rut_user' => $rut_user,
                'id_prevention_type_detail' => $request->get('id_prevention_type_detail'),
                'assurance' => $request->get('assurance'),
                'id_location' => $request->get('id_location'),
                'way_number' => $request->get('way_number'),
                'request_date' => $request->get('request_date'),
                'id_status' => 11,
                'comments' => $request->get('comments'),
                'carer' => $request->get('carer'),
                'id_motive' => $request->get('id_motive'),
                'id_dedication' => $request->get('id_dedication'),
                'id_remuneration' => $request->get('id_remuneration'),
                'dir_wots' =>  $request->get('dir_wots'),
                'latitude' =>  $request->get('latitude'),
                'longitude' => $request->get('longitude'),
                'rut_employee' => $request->get('rut_employee'),
                'approve' => null,
                'id_request' => $id_request,
                'id_mod_status' => 1,
                'reg_type' => $request->get('reg_type'),
                'prevention_type' => $request->get('prevention_type'),
                'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken()),
                'id_status_emp' => $request->get('id_status_emp')
            ]);
        }
        elseif ($request->reg_type != 'WOTS') {
            $mod_sol = ModifySolicitude::create([
                'rut_user' => $rut_user,
                'id_prevention_type_detail' => $request->get('id_prevention_type_detail'),
                'assurance' => $request->get('assurance'),
                'id_location' => $request->get('id_location'),
                'way_number' => $request->get('way_number'),
                'id_property_type' => $request->get('id_property_type'),
                'property_number' => $request->get('property_number'),
                'request_date' => $request->get('request_date'),
                'id_status' => 11,
                'comments' => $request->get('comments'),
                'carer' => $request->get('carer'),
                'id_motive' => $request->get('id_motive'),
                'id_dedication' => $request->get('id_dedication'),
                'id_remuneration' => $request->get('id_remuneration'),
                'rut_employee' => $request->get('rut_employee'),
                'approve' => null,
                'id_request' => $id_request,
                'id_mod_status' => 1,
                'reg_type' => $request->get('reg_type'),
                'prevention_type' => $request->get('prevention_type'),
                'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken()),
                'id_status_emp' => $request->get('id_status_emp')
            ]);
        }

        $mod_status = DB::table('solicitudes')->where('id', $id_request)->update(['id_mod_status' => 1]);

        return response()->json($mod_sol, 200);
    }

    public function approveSolChanges(Request $request, $id_solicitude, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $employee = DB::table('users')->where('rut', $rut)->value('employee');

        if ($employee == false) {
            return response()->json(['error' => 'This user is not a employee'], 400);
        }

        $record = ModifySolicitude::where([
            'id_solicitude' => $id_solicitude
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifySolicitude::query()->where('id_solicitude', $id_solicitude)
                                ->update([
                                    'approve' => true,
                                    'id_mod_status' => 2,
                                    'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                                ]);

        /* $mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)
                        ->update([
                            'id_mod_status' => 2,
                        ]); */
                        
        $id_request = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('id_request');
        
        //$mod_sol_status = DB::table('solicitudes')->where('id', $id_request)->update(['id_mod_status' => 2]);
        
        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function rejectSolChanges(Request $request, $id_solicitude, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $employee = DB::table('users')->where('rut', $rut)->value('employee');

        if ($employee == false) {
            return response()->json(['error' => 'This user is not a employee'], 400);
        }

        $record = ModifySolicitude::where([
            'id_solicitude' =>$id_solicitude
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifySolicitude::query()->where('id_solicitude', $id_solicitude)
                                ->update(['approve' => false,
                                          'id_mod_status' => 4,
                                          'rejected_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                                 ]);
    
        //$mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->update(['id_mod_status' => 4]);
        $id_request = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('id_request');
        
        //$mod_sol_status = DB::table('solicitudes')->where('id', $id_request)->update(['id_mod_status' => 4]);

        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function CompSolData(Request $request, $id_request)
    {
        $id_solicitude = DB::table('modify_solicitudes')->where('id_request', $id_request)->orderByDesc('id_solicitude')->limit(1)->value('id_solicitude');

        if ($request->reg_type == 'WOTS') {
            $compare = DB::select(
                "SELECT 
                case when a.id_prevention_type_detail = $request->id_prevention_type_detail then true else false end as comp_id_prevention_type_detail,
                case when a.assurance = '$request->assurance' then true else false end as comp_assurance,
                case when a.id_location = $request->id_location then true else false end as comp_id_location,
                case when a.way_number = $request->way_number then true else false end as comp_way_number,
                case when a.id_property_type = $request->id_property_type then true else false end as comp_id_property_type,
                case when a.property_number = '$request->property_number' then true else false end as comp_property_number,
                case when a.request_date = '$request->request_date' then true else false end as comp_request_date,
                case when a.id_status = $request->id_status then true else false end as comp_id_status,
                case when a.dir_wots = '$request->dir_wots' then true else false end as comp_dir_wots,
                case when a.latitude = $request->latitude then true else false end as comp_latitude,
                case when a.longitude = $request->longitude then true else false end as comp_longitude,
                case when a.id_request = $request->id_request then true else false end as comp_id_request
                FROM consultants.modify_solicitudes as a 
                WHERE a.id_solicitude = $id_solicitude AND a.id_request = $id_request");
        }
        elseif ($request->reg_type != 'WOTS') {
            $compare = DB::select(
                "SELECT 
                case when a.id_prevention_type_detail = $request->id_prevention_type_detail then true else false end as comp_id_prevention_type_detail,
                case when a.assurance = '$request->assurance' then true else false end as comp_assurance,
                case when a.id_location = $request->id_location then true else false end as comp_id_location,
                case when a.way_number = $request->way_number then true else false end as comp_way_number,
                case when a.id_property_type = $request->id_property_type then true else false end as comp_id_property_type,
                case when a.property_number = '$request->property_number' then true else false end as comp_property_number,
                case when a.request_date = '$request->request_date' then true else false end as comp_request_date,
                case when a.id_status = $request->id_status then true else false end as comp_id_status,
                case when a.id_request = $request->id_request then true else false end as comp_id_request
                FROM consultants.modify_solicitudes as a 
                WHERE a.id_solicitude = $id_solicitude AND a.id_request = $id_request");
        }

        return response()->json($compare, 200);
    }

    public function updateSolData(Request $request, $id_request, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $id_solicitude = DB::table('modify_solicitudes')->where('id_request', $id_request)->orderByDesc('id_solicitude')->limit(1)->value('id_solicitude');

        $gate = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('approve');

      /*   if ($gate == false) {
            return response()->json(['error' => 'This record is not aproved'], 400);
        }
         */

        if ($gate == true) {
            if ($request->reg_type == 'WOTS') {
            
                $update = DB::table('solicitudes')->where('id', $id_request)->update([
                    'id_prevention_type_detail' => $request->id_prevention_type_detail,
                    'assurance' => $request->assurance,
                    'id_location' => $request->id_location,
                    'way_number' => $request->way_number,
                    'id_property_type' => $request->id_property_type,
                    'property_number' => $request->property_number,
                    'request_date' => $request->request_date,
                    'dir_wots' => $request->dir_wots,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'reg_type' => $request->reg_type,
                    'id_mod_status' => 3,
                    'id_status_emp' => $request->id_status_emp
                ]);
                $mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->update(['id_mod_status' => 3]);
            }
            elseif ($request->reg_type != 'WOTS') {
    
                $update = DB::table('solicitudes')->where('id', $id_request)->update([
                    'id_prevention_type_detail' => $request->id_prevention_type_detail,
                    'assurance' => $request->assurance,
                    'id_location' => $request->id_location,
                    'way_number' => $request->way_number,
                    'id_property_type' => $request->id_property_type,
                    'property_number' => $request->property_number,
                    'request_date' => $request->request_date,
                    'reg_type' => $request->reg_type,
                    'id_mod_status' => 3,
                    'id_status_emp' => $request->id_status_emp
                ]);
                $mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->update(['id_mod_status' => 3]);
            }
        }elseif ($gate == false) {
            $update = solicitudes::query()->where('id', $id_request)->update(['id_mod_status' => 4]);
        }
        

        return response()->json([
            'message' => 'Data updated successfull',
            'approve_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]); 
    }

    public function updateSolDataVIP(Request $request, $id_request, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $id_solicitude = DB::table('modify_solicitudes')->where('id_request', $id_request)->orderByDesc('id_solicitude')->limit(1)->value('id_solicitude');

        if ($request->reg_type == 'WOTS') {
            
            $update = DB::table('solicitudes')->where('id', $id_request)->update([
                'id_prevention_type_detail' => $request->id_prevention_type_detail,
                'assurance' => $request->assurance,
                'id_location' => $request->id_location,
                'way_number' => $request->way_number,
                'id_property_type' => $request->id_property_type,
                'property_number' => $request->property_number,
                'request_date' => $request->request_date,
                'dir_wots' => $request->dir_wots,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'reg_type' => $request->reg_type,
                'id_mod_status' => 3,
                'id_status_emp' => $request->id_status_emp
            ]);
            $mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->update(['id_mod_status' => 3]);
        }
        elseif ($request->reg_type != 'WOTS') {

            $update = DB::table('solicitudes')->where('id', $id_request)->update([
                'id_prevention_type_detail' => $request->id_prevention_type_detail,
                'assurance' => $request->assurance,
                'id_location' => $request->id_location,
                'way_number' => $request->way_number,
                'id_property_type' => $request->id_property_type,
                'property_number' => $request->property_number,
                'request_date' => $request->request_date,
                'reg_type' => $request->reg_type,
                'id_mod_status' => 3,
                'id_status_emp' => $request->id_status_emp,
            ]);
            $mod_status = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->update(['id_mod_status' => 3]);
        }

        return response()->json([
            'message' => 'Data updated successfull',
            'approve_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]); 
    }

    public function updateExtraData(Request $request, $id_request, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $id_solicitude = DB::table('modify_solicitudes')->where('id_request', $id_request)->orderByDesc('id_solicitude')->limit(1)->value('id_solicitude');

        /* $gate = DB::table('modify_solicitudes')->where('id_solicitude', $id_solicitude)->value('approve');

        if ($gate == false) {
            return response()->json(['error' => 'This record is not aproved'], 400);
        } */

        try {
            $update_mod = DB::table('modify_solicitudes')->where('id_request', $id_request)->update([
                'comments' => $request->comments,
                'carer' => $request->carer,
                'id_motive' => $request->id_motive,
                'id_dedication' => $request->id_dedication,
                'id_remuneration' => $request->id_remuneration,
                'id_mod_status' => 3
            ]);

            $update = DB::table('solicitudes')->where('id', $id_request)->update([
                'comments' => $request->comments,
                'carer' => $request->carer,
                'id_motive' => $request->id_motive,
                'id_dedication' => $request->id_dedication,
                'id_remuneration' => $request->id_remuneration,
                'id_mod_status' => 3
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        } 

        return response()->json([
            'message' => 'Updated successfull',
            'approve_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]);
    }

    /* public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    } */
}
