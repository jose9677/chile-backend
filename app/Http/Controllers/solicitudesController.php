<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\asignarVisitadorMailable;
use App\Mail\confirmarCitaMailable;
use App\Mail\reprogramarCitaMailable;
use App\Models\solicitudes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

            $user = new User();
            
            //ARREGLAR ESTE CORREO
            $send = new reprogramarCitaMailable($user->names, $user->lastname, $id);
            Mail::to('testing@angulo-group.com')->send($send);

            return response()->json([
                'message' => 'Request '.$id. ' was changed to Reprogram'
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

            $user = new User();
            //$user = User::findOrFail();

            //ARREGLAR ESTE CORREO
            $send = new confirmarCitaMailable($user->names, $user->lastname, $id);
            Mail::to('testing@angulo-group.com')->send($send);

            return response()->json(['message' => 'Request '.$id. ' was assigned'], 200);
        }else {
            return response()->json(['error'=> 'Bad status'], 400);
        }
    }

    public function add_visitor(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $sol = solicitudes::findOrFail($id);

        $user = User::where([
            'rut' => $request['rut']
        ])->get();

        if (!$user->count()) {
            return response()->json(['error' => 'User does not exists into Data Base'],400);
        }

        $user = User::query()->where(['rut' => $request->input('rut'), 'employee' => true])->first();
        if (!$user) {
            return response()->json(['error' => 'This user is not a employee'],400);
        }
        elseif ($user) {
            $sol->rut_employee = $request->rut;
            $sol->save();
        }
        $query = DB::select("SELECT * FROM users WHERE rut = '$request->rut'");
        
        $user = new User();
        $user = User::findOrFail($request->rut);

        //ARREGLAR ESTE CORREO
        $send = new asignarVisitadorMailable($user->names, $user->lastname ,$id);
        Mail::to('testing@angulo-group.com')->send($send);

        return response()->json([
            'message' => 'Visitor '.$request->rut. ' was assigned to request '.$id,
            'data_visitor' => $query
            ],200);
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
     public function requestAssigned()
     {
        $query_count = DB::select("SELECT COUNT(*) FROM consultants.solicitudes");
        $query_approveRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 6");
        $query_rejectRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 7");

         $query = DB::select(
            "SELECT 
            a.id as id_request,
            a.created_at as request_date, 
            a.rut_user,
            b.names as user_name,
            b.lastname as user_lastname,
            c.id as id_location,
            c.street,
			a.way_number,
            d.id as id_property_type,
            d.description,
			a.property_number,
            e.rut as assistant_rut,
            e.names as assistant_name,
            e.lastname as assistant_lastname,
			f.rut as supervisor_rut,
            f.names as supervisor_names,
            f.lastname as supervisor_lastname,
            g.id as id_status,
            g.status
            FROM consultants.solicitudes as a 
            LEFT JOIN consultants.users as b ON a.rut_user = b.rut
            LEFT JOIN consultants.locations as c ON a.id_location = c.id
            LEFT JOIN consultants.property_types as d ON a.id_property_type = d.id
            LEFT JOIN consultants.users as e ON e.rut = a.rut_employee
            LEFT JOIN consultants.users as f ON f.rut = e.supervisor_rut
            LEFT JOIN consultants.status as g ON a.id_status = g.id"
         );
 
         return response()->json(
            [
                'count_solicitudes' => $query_count,
                'approve_Request' => $query_approveRequest,
                'reject_Request' => $query_rejectRequest,
                'data_complete' => $query
            ]);
     }
}
