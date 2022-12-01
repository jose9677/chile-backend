<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class solicitudesController extends Controller
{
    public function createRequest(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
           'id_prevention_type_detail' => 'required',
           'assurance' => 'required',
           'id_location' => 'required',
           'way_number' => 'required',
           'id_property_type' => 'required',
           'property_number' => 'required',
           //'terms_condition' => 'required',
           'request_date' => 'required',
           'id_status' => 'required',
           /*'applicant_at_home' => 'required',
           'prevention_is_valid' => 'required',
           'location_is_valid' => 'required',*/
           'comments' => 'required',
           //'is_active' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = solicitudes::create([
            'id_prevention_type_detail' => $request->get('id_prevention_type_detail'),
            'assurance' => $request->get('assurance'),
            'id_location' => $request->get('id_location'),
            'way_number' => $request->get('way_number'),
            'id_property_type' => $request->get('id_property_type'),
            'property_number' => $request->get('property_number'),
            'terms_condition' => false,
            'request_date' => time(),
            'id_status' => 1,
            'applicant_at_home' => false,
            'prevention_is_valid' => false,
            'location_is_valid' => false,
            'comments' => $request->get('comments'),
            'is_active' => false
        ]);
        
        return response()->json($data, 200);
    }

    public function detailRequest()
    {
        $data = solicitudes::all();
        return response()->json($data, 200);
    }

    public function getRequest($id)
    {
        $data = new solicitudes();
        $dataFound = $data->findOrFail($id);

        return response()->json($dataFound, 200);
    }

    public function updateRequest(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
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
            'id_active' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->id_prevention_type_detail = $request->id_prevention_type_detail;
        $data->assurance = $request->assurance;
        $data->id_location = $request->id_location;
        $data->way_number = $request->way_number;
        $data->id_property_type = $request->id_property_type;
        $data->terms_condition = $request->terms_condition;
        $data->request_date = $request->request_date;
        $data->id_status = $request->id_status;
        $data->applicant_at_home = $request->applicant_at_home;
        $data->prevention_is_valid = $request->prevention_is_valid;
        $data->location_is_valid = $request->location_is_valid;
        $data->comments = $request->comments;
        $data->is_active = $request->is_active;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Update Failed'], 400);
        }

        return response()->json([
            'message' => 'Request Updated successfull',
            'data_request' => $data->id
        ], 200);
    }

    public function deleteRequest($id)
    {
        $data = solicitudes::find($id);

        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Request Does Not exists in Data Base'], 400);
        }

        return response()->json(['message' => 'Request deleted successfull'], 200);
    }

    public function confirmNeightbor($id)
    {
        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->applicant_at_home = true;
        $data->id_status = 4;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Neightbor has confirmed his date'], 200);
    }

    public function programDate(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'request_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->request_date = $request->request_date;
        $data->id_status = 3;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['erro' => 'Bad Requets'], 400);
        }
        
        return response()->json([
            'message' => 'Request '.$id. ' assigned for the date '.$request->request_date
        ], 200);
    }

    public function reprogramDate(Request $request, $id)
    {
        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        if ($data) {
            $data = solicitudes::query()->where(['id' => $id])->update(['id_status' => 5]);
            return response()->json([
                'message' => 'Request id_request was changed to Reprogram'
            ], 200);   
       }
    }

    public function programVisitDate(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'visit_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = solicitudes::findOrFail($id);
        
        if ($data->id_status == 2) {
            solicitudes::query()->where(['id' => $id])->update(['visit_date' => $request->visit_date,
            'id_status' => 3]);

            return response()->json(['message' => 'Request '.$id. ' was assigned'], 200);
        }

        if ($data->id_status == 5) {
            solicitudes::query()->where(['id' => $id])->update(['visit_date' => $request->visit_date,
            'id_status' => 4]);

            return response()->json(['message' => 'Request '.$id. ' was assigned'], 200);
        }else {
            return response()->json(['error'=> 'Bad status'], 400);
        }
    }

    public function approveRequest($id)
    {
        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->id_status = 6;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Date has been approved'], 200);
    }

    public function rejectRequest($id)
    {
        
        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->id_status = 7;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Date has been rejected'], 200);
    }

    //JOINS

     //This Method get all data about Users and their Request assigned throught rut
     public function joinUsersRequest($rut)
     {
        /* $query = DB::select(
             "SELECT 
             a.*,
             c.*
             FROM consultants.users as a
             JOIN consultants.users_requests as b
             USING (rut)
             JOIN consultants.solicitudes as c ON b.id_request = c.id
             WHERE a.rut = '$rut'"
         );*/
 
         //New query
         $query = DB::select(
            "SELECT 
            a.rut,
            a.names,
            a.lastname,
            b.id as id_request,
            b.id_prevention_type_detail,
            b.visit_date,
            b.created_at as solicitude_date,
            b.request_date,
            c.prevention_detail,
            d.prevention_type,
            b.id_status,
            f.status, 
            e.rut as visitor_rut,
            e.names as employee_name, 
            e.lastname as employee_lastname,
            g.id as id_document,
            g.document_path, 
            h.document_type
            FROM consultants.users as a 
            JOIN consultants.solicitudes as b ON a.rut = b.rut_user
            JOIN consultants.preventions_details as c ON b.id_prevention_type_detail = c.id
            JOIN consultants.preventions_types as d ON c.id_prevention_type = d.id
            JOIN consultants.users as e ON b.rut_employee = e.rut
            JOIN consultants.status as f ON b.id_status = f.id
            JOIN consultants.documents as g ON g.id_request = b.id
            JOIN consultants.documents_types as h ON h.id = g.id_document_type
            WHERE a.rut = '$rut'"
         );
 
         return response()->json($query);
     }
}
