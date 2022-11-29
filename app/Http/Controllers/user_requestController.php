<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\solicitudes;
use App\Models\User;
use App\Models\users_requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class user_requestController extends Controller
{
    public function add_request(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'id_request' => 'required',
            'employee_rut' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new users_requests();

        $data->rut = $request->rut;
        $data->id_request = $request->id_request;
        $data->employee_rut = $request->employee_rut;
        
        
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Request successfull'], 200);
    }

    public function add_employeeRut(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'id_request' => 'required',
            'employee_rut' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new users_requests();
        
        $data = users_requests::where([
            'rut' => $request['rut'],
            'id_request' => $request['id_request']
        ])->get();

        if (!$data->count()) {
            return response()->json(['error' => 'Record does not exists'], 400);
        }

        $data = new users_requests();
        $data->employee_rut = $request->employee_rut;
         
        $data = users_requests::query()->where([
            'rut' => $request->input('rut'), 'id_request' => $request->input('id_request')
            ])->update(['employee_rut' => $request->employee_rut]);

        $sol = new solicitudes();
        $sol = solicitudes::query()->where(['id' => $request->input('id_request')])->update(['id_status' => 2]);
        
        return response()->json(['data' => $data], 200);
        
        //$query = DB::select("SELECT rut FROM users WHERE employee = true ORDER BY rut");
        /*for ($i=0; $i < 2 ; $i++) { 
            if ($query[$i] == $request->employee_rut ) {
                
                $data = users_requests::query()->where([
                'rut' => $request->input('rut'), 'id_request' => $request->input('id_request')
                ])->update(['employee_rut' => $request->employee_rut]);
                
            }
        }*/
    }
    
    public function detailUserRequest()
    {
            /*$query = DB::select(            
            "SELECT 
            b.rut,
            b.employee_rut,
            c.names as user_name,
            c.lastname as user_lastname,
            a.id as id_request,
            a.id_prevention_type_detail,
            g.prevention_detail,
            h.prevention_type,
            a.assurance,
            a.id_location,
            d.street,
            a.way_number,
            a.id_property_type,
            f.description,
            a.property_number,
            a.terms_condition,
            a.request_date, 
            a.id_status,
            e.status,
            a.applicant_at_home,
            a.prevention_is_valid,
            a.location_is_valid,
            a.comments,
            a.is_active,
            a.rut_match
            FROM consultants.solicitudes as a 
            JOIN consultants.users_requests as b ON a.id = b.id_request
            JOIN consultants.users as c ON b.rut = c.rut
            JOIN consultants.locations as d ON a.id_location = d.id
            JOIN consultants.status as e ON a.id_status = e.id
            JOIN consultants.property_types as f ON a.id_property_type = f.id
            JOIN consultants.preventions_details as g ON a.id_prevention_type_detail = g.id
            JOIN consultants.preventions_types as h ON g.id_prevention_type = h.id"
        );*/

        //Query Cristian
        $query = DB::select(
            "SELECT 
            b.rut,
            b.employee_rut,
            i.names as asistant_name,
            i.lastname as asistant_lastname,
            c.names as user_name,
            c.lastname as user_lastname,
            a.id as id_request,
            a.id_prevention_type_detail,
            g.prevention_detail,
            h.prevention_type,
            a.assurance,
            a.id_location,
            d.street,
            a.way_number,
            a.id_property_type,
            f.description,
            a.property_number,
            a.terms_condition,
            a.request_date, 
            a.id_status,
            e.status,
            a.applicant_at_home,
            a.prevention_is_valid,
            a.location_is_valid,
            a.comments,
            a.is_active,
            a.rut_match
            FROM consultants.solicitudes as a 
            LEFT JOIN consultants.users_requests as b ON a.id = b.id_request
            LEFT JOIN consultants.users as c ON b.rut = c.rut
            LEFT JOIN consultants.locations as d ON a.id_location = d.id
            LEFT JOIN consultants.status as e ON a.id_status = e.id
            LEFT JOIN consultants.property_types as f ON a.id_property_type = f.id
            LEFT JOIN consultants.preventions_details as g ON a.id_prevention_type_detail = g.id
            LEFT JOIN consultants.preventions_types as h ON g.id_prevention_type = h.id
            LEFT JOIN consultants.users as i ON b.employee_rut = i.rut"
        );

        return response()->json([
            'data_User_Request' => $query]);
    }

    //This Method get all data about Users and their Request assigned throught rut
    public function joinUsersRequest($rut)
    {
        $query = DB::select(
            "SELECT 
            a.*,
            c.*
            FROM consultants.users as a
            JOIN consultants.users_requests as b
            USING (rut)
            JOIN consultants.solicitudes as c ON b.id_request = c.id
            WHERE a.rut = '$rut'"
        );

        return response()->json($query);
    }

    //This Method get all data about Users and their Request assigned throught id_request
    public function joinRequestUsers($id)
    {
        $query = DB::select(
            "SELECT 
            a.*,
            c.*
            FROM consultants.users as a
            JOIN consultants.users_requests as b
            USING (rut)
            JOIN consultants.solicitudes as c ON b.id_request = c.id
            WHERE c.id = $id"
        );

        return response()->json($query);
    }

    public function joinRequestEmployee($employee_rut)
    {
        $data = new User();
        $data = User::findOrFail($employee_rut);

        $query = DB::select(
            "SELECT 
            a.*,
            c.*
            FROM consultants.users as a
            JOIN consultants.users_requests as b
            USING (rut)
            JOIN consultants.solicitudes as c ON b.id_request = c.id
            WHERE b.employee_rut = '$employee_rut'"
        );

        return response()->json([
            'data_employe' => $data,
            'data_Neightbor' => $query
        ]);
    }
}
