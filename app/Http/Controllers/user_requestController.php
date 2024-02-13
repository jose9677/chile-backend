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
    
    public function detailNeightborRequest($rut_user)
    {
        $query = DB::select(
            "SELECT 
            a.id as id_request,
            a.rut_user,
            a.created_at as request_date,
            a.id_prevention_type_detail,
            b.prevention_detail,
            b.id_prevention_type,
            c.prevention_type,
            a.visit_date,
            d.rut,
            d.names as assistant_names,
            d.lastname as assistant_lastname,
            a.id_status,
            e.status,
            a.id_status_emp,
            f.status as status_emp
            FROM consultants.solicitudes as a 
            JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
            JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
            LEFT JOIN consultants.users as d ON a.rut_employee = d.rut
            JOIN consultants.status as e ON a.id_status = e.id
            JOIN consultants.status as f ON a.id_status_emp = f.id
            WHERE a.rut_user = '$rut_user'"

        );

        return response()->json([
            'data_User_Request' => $query]);
    }

    //This Method get all data about Users and their Request assigned throught id_request
    public function joinRequestUsers($id)
    {
        $rut = DB::table('solicitudes')->where('id', $id)->value('rut_user');
        $origin = DB::table('users')->where('rut', $rut)->value('origin');

        if (empty($origin)) {
            $query = DB::select(
                "SELECT 
                a.created_at as request_date,
                b.rut,
                b.names,
                b.lastname,
                b.landline,
                b.mobile_phone,
                a.id_status,
                c.status,
                a.id_location,
                a.way_number,
                d.street,
                a.id_property_type,
                e.description,
                a.property_number,
                f.rut as rut_assistant,
                f.names as names_assistant,
                f.lastname as lastname_assistant,
                a.visit_date,
                a.id_prevention_type_detail,
                g.prevention_detail,
                h.prevention_type,
                i.id as id_document,
                i.id_document_type,
                i.document_path,
                'http://54.189.74.47/storage/posts/'||i.document_path as doc_srv_path,
                i.document_ext,
                j.document_type
                FROM consultants.solicitudes as a
                LEFT JOIN consultants.users as b ON a.rut_user = b.rut
                LEFT JOIN consultants.status as c ON c.id = a.id_status
                LEFT JOIN consultants.locations as d ON a.id_location = d.id
                LEFT JOIN consultants.property_types as e ON a.id_property_type = e.id
                LEFT JOIN consultants.users as f ON a.rut_employee = f.rut
                LEFT JOIN consultants.preventions_details as g ON a.id_prevention_type_detail = g.id
                LEFT JOIN consultants.preventions_types as h ON g.id_prevention_type = h.id
                LEFT JOIN consultants.documents as i ON i.id_request = a.id AND i.active = true
                LEFT JOIN consultants.documents_types as j ON j.id = i.id_document_type
                WHERE a.id = $id "
            );    
        }elseif ($origin) {
            $query = DB::select(
                "SELECT 
                a.created_at as request_date,
                b.rut,
                b.names,
                b.lastname,
                b.landline,
                b.mobile_phone,
                a.id_status,
                c.status,
                a.id_location,
                '' as way_number,
                a.dir_wots as street,
                a.id_property_type,
                e.description,
                a.property_number,
                f.rut as rut_assistant,
                f.names as names_assistant,
                f.lastname as lastname_assistant,
                a.visit_date,
                a.id_prevention_type_detail,
                g.prevention_detail,
                h.prevention_type,
                i.id as id_document,
                i.id_document_type,
                i.document_path,
                'http://54.189.74.47/storage/posts/'||i.document_path as doc_srv_path,
                i.document_ext,
                j.document_type
                FROM consultants.solicitudes as a
                LEFT JOIN consultants.users as b ON a.rut_user = b.rut
                LEFT JOIN consultants.status as c ON c.id = a.id_status
                LEFT JOIN consultants.locations as d ON a.id_location = d.id
                LEFT JOIN consultants.property_types as e ON a.id_property_type = e.id
                LEFT JOIN consultants.users as f ON a.rut_employee = f.rut
                LEFT JOIN consultants.preventions_details as g ON a.id_prevention_type_detail = g.id
                LEFT JOIN consultants.preventions_types as h ON g.id_prevention_type = h.id
                LEFT JOIN consultants.documents as i ON i.id_request = a.id AND i.active = true
                LEFT JOIN consultants.documents_types as j ON j.id = i.id_document_type
                WHERE a.id = $id"
            );
        }
        
        return response()->json($query);
    }

    public function joinRequestEmployee($rut_employee)
    {
        $data = new User();
        $data = User::findOrFail($rut_employee);

        $query = DB::select(
            "SELECT 
            a.id,
            a.created_at, 
            a.rut_user,
            b.names,
            b.lastname,
            a.rut_employee, 
            f.names as assistant_name,
            f.lastname as assistant_lastname,
            a.visit_date,
            a.id_location,
            d.street,
            a.way_number,
            a.id_property_type,
            c.description,
            a.property_number,
            a.id_status,
            e.status,
            a.id_status_emp,
            g.status as status_emp
            FROM consultants.solicitudes as a 
            JOIN consultants.users as b ON a.rut_user = b.rut
            JOIN consultants.property_types as c ON c.id = a.id_property_type
            JOIN consultants.locations as d ON d.id = a.id_location
            JOIN consultants.status as e ON e.id = a.id_status
            JOIN consultants.users as f ON f.rut = a.rut_employee
            JOIN consultants.status as g ON a.id_status = e.id
            WHERE a.rut_employee = '$rut_employee'"
        );

        return response()->json([
            'data_employe' => $data,
            'data_Neightbor' => $query
        ]);
    }
}
