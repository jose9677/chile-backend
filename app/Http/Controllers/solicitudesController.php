<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\aprobarSolicitud;
use App\Mail\aprobarSolicitudMailable;
use App\Mail\asignarSolicitudMailable;
use App\Mail\asignarVisitadorMailable;
use App\Mail\confirmarCitaMailable;
use App\Mail\rechazarSolicitudMailable;
use App\Mail\reprogramarCitaMailable;
use App\Models\solicitudes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
class solicitudesController extends Controller
{
    public function createRequest(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
           /* 'rut_user' => 'required',
            'id_prevention_type_detail' => 'required',
           'assurance' => 'required',
           'id_location' => 'required',
           'way_number' => 'required',
           'id_property_type' => 'required',
           'property_number' => 'required', */
           //'terms_condition' => 'required',
           //'request_date' => 'required',
           //'id_status' => 'required',
           /*'applicant_at_home' => 'required',
           'prevention_is_valid' => 'required',
           'location_is_valid' => 'required',*/
           //'comments' => 'required',
           //'is_active' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id_sol = DB::table('solicitudes')->where('rut_user', $request->rut_user)->orderByDesc('id')->limit(1)->value('id');
        $id_status = DB::table('solicitudes')->where('id', $id_sol)->value('id_status');
        
        if (empty($id_sol)) {
            $sol = new solicitudes();
            $sol->rut_user = Str::upper($request->rut_user);
            $sol->id_prevention_type_detail = $request->get('id_prevention_type_detail');
            $sol->assurance = $request->get('assurance');
            $sol->id_location = $request->get('id_location');
            $sol->way_number = $request->get('way_number');
            $sol->id_property_type = $request->get('id_property_type');
            $sol->property_number = $request->get('property_number');
            $sol->terms_condition = $request->get('terms_condition');
            //$sol->request_date = time();
            $sol->id_status = 13;//RENOVACION...
            $sol->applicant_at_home = false;
            $sol->prevention_is_valid = false;
            $sol->location_is_valid = false;
            $sol->comments = $request->get('comments');
            $sol->is_active = true;
            $sol->rut_match = false;
            $sol->carer = $request->get('carer');
            $sol->id_motive = $request->get('id_motive');
            $sol->id_dedication = $request->get('id_dedication');
            $sol->id_remuneration = $request->get('id_remuneration');
            // $sol->admission_date = $request->get('admission_date');
            //$sol->reg_type = 'REGISTRO POR PAGINA';
            $sol->id_status_emp = 13;//RENOVACION...

            if ($request->terms_condition == false) {
                return response()->json(['error' => 'User must to accept terms condition'], 400);
            }
            elseif ($request->terms_condition == true) {
                $sol->save();
            }

        }elseif ($id_status == 7 or $id_status == 9 or $id_status == 14) {
            $sol = new solicitudes();
            $sol->rut_user = Str::upper($request->rut);
            $sol->id_prevention_type_detail = $request->get('id_prevention_type_detail');
            $sol->assurance = $request->get('assurance');
            $sol->id_location = $request->get('id_location');
            $sol->way_number = $request->get('way_number');
            $sol->id_property_type = $request->get('id_property_type');
            $sol->property_number = $request->get('property_number');
            $sol->terms_condition = $request->get('terms_condition');
            //$sol->request_date = time();
            $sol->id_status = 13; //RENOVACION...
            $sol->applicant_at_home = false;
            $sol->prevention_is_valid = false;
            $sol->location_is_valid = false;
            $sol->comments = $request->get('comments');
            $sol->is_active = true;
            $sol->rut_match = false;
            $sol->carer = $request->get('carer');
            $sol->id_motive = $request->get('id_motive');
            $sol->id_dedication = $request->get('id_dedication');
            $sol->id_remuneration = $request->get('id_remuneration');
            // $sol->admission_date = $request->get('admission_date');
            //$sol->reg_type = 'REGISTRO POR PAGINA';
            $sol->id_status_emp = 13; //RENOVACION...

            if ($request->terms_condition == false) {
                return response()->json(['error' => 'User must to accept terms condition'], 400);
            }
            elseif ($request->terms_condition == true) {
                $sol->save();
            }
        }else {
            return response()->json(['error' => 'This user has a solicitude active'], 400);
        }
        
        return response()->json($sol, 200);
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
            'is_active' => 'required',
            'carer' => 'required',
            'motive' => 'required',
            'dedication' => 'required',
            'remuneration' => 'required',
            'admission_date' => 'required'
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
        $data->rut_match = $request->rut_match;
        $data->carer = $request->carer;
        $data->motive = $request->motive;
        $data->dedication = $request->dedication;
        $data->remuneration = $request->remuneration;
        $data->admission_date = $request->admission_date;

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

    public function programDate($id)
    {
        /*Log::info($request);
        $validator = Validator::make($request->all(), [
            'request_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }*/

        $data = new solicitudes();
        $data = solicitudes::findOrFail($id);

        $data->request_date = date('Y-m-d');
        $data->id_status = 3;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Requets'], 400);
        }
        
        return response()->json([
            'message' => 'Request '.$id. ' assigned for the date '.$data->request_date
        ], 200);
    }

    public function reprogramDate(Request $request, $id)
    {
        $data = solicitudes::findOrFail($id);
        
        if ($data) {
            $data = solicitudes::query()->where(['id' => $id])->update(['id_status' => 5]);

            $rut = DB::table('solicitudes')->where('id', $id)->value('rut_employee');
            $names = DB::table('users')->where('rut', $rut)->value('names');
            $lastname = DB::table('users')->where('rut', $rut)->value('lastname');
            $email = DB::table('users')->where('rut', $rut)->value('email');

            try {    
                $mc = new MailController;

                $params = [
                    'names' => $names,
                    'lastname' => $lastname,
                    'id' => $id
                ];
        
                $ret = $mc->reprogramRequestDate($email, $id, $params);
                if($ret !== true){
                    abort(500, $ret);
                }

            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new reprogramarCitaMailable($names, $lastname, $id);
            //Mail::to($email)->send($send);

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

            $rut = DB::table('solicitudes')->where('id', $id)->value('rut_user');
            $rut_employee = DB::table('solicitudes')->where('id', $id)->value('rut_employee');
            $names = DB::table('users')->where('rut', $rut_employee)->value('names');
            $lastname = DB::table('users')->where('rut', $rut_employee)->value('lastname');
            $email = DB::table('users')->where('rut', $rut)->value('email');

            try {    
                $mc = new MailController;

                $params = [
                    'id' => $id,
                    'names' => $names,
                    'lastname' => $lastname,
                    'visit_date' => $request->visit_date
                ];
        
                $ret = $mc->visitDate($email, $id, $params);
                if($ret !== true){
                    abort(500, $ret);
                }

            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new asignarSolicitudMailable($id, $names, $lastname, $request->visit_date);
            //Mail::to($email)->send($send);

            return response()->json(['message' => 'Request '.$id. ' was assigned'], 200);
        }

        if ($data->id_status == 5) {
            solicitudes::query()->where(['id' => $id])->update(['visit_date' => $request->visit_date,
            'id_status' => 4]);

            $rut = DB::table('solicitudes')->where('id', $id)->value('rut_employee');
            $names = DB::table('users')->where('rut', $rut)->value('names');
            $lastname = DB::table('users')->where('rut', $rut)->value('lastname');
            $email = DB::table('users')->where('rut', $rut)->value('email');

            try {
                $mc = new MailController;

                $params = [
                    'id' => $id,
                    'names' => $names,
                    'lastname' => $lastname
                ];
        
                $ret = $mc->confirmVisit($email, $id, $params);
                if($ret !== true){
                    abort(500, $ret);
                }

            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new confirmarCitaMailable($names, $lastname, $id);
            //Mail::to($email)->send($send);

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
            $sol->id_status = 2;
            $sol->save();
        }
        $query = DB::select("SELECT * FROM users WHERE rut = '$request->rut'");
        
        $user = new User();
        $user = User::findOrFail($request->rut);

        $email = DB::table('users')->where('rut', $request->input('rut'))->value('email');

        try {
            $mc = new MailController;

            $params = [
                'id' => $id,
                'names' => $user->names,
                'lastname' => $user->lastname
            ];
    
            $ret = $mc->addVisitor($email, $id, $params);
            if($ret !== true){
                abort(500, $ret);
            }

        } catch (\Throwable $th) {
            return response()->json(['Error' => 'Failed to send Email'], 400);
        }

        //$send = new asignarVisitadorMailable($user->names, $user->lastname ,$id);
        //Mail::to($email)->send($send);

        return response()->json([
            'message' => 'Visitor '.$request->rut. ' was assigned to request '.$id,
            'data_visitor' => $query
            ],200);
    }

    //This method get every data about a visitor assigned to any request
    public function detailsVisitor(Request $request, $rut_employee)
    {
        /*Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut_employee' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new solicitudes();
        $data->rut_employee = $request->rut_employee;*/

        $query = DB::select(
            "SELECT 
            a.id as id_request,
            b.*,
            'http://54.189.74.47/storage/profile_pic/'||b.profile_pic as profile_pic_srv_path
            from consultants.solicitudes as a 
            JOIN consultants.users as b ON a.rut_employee = b.rut
            WHERE a.rut_employee = '$rut_employee'"
        );

        return response()->json($query);
    }

    public function approveRequest(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'applicant_at_home' => 'required',
            'prevention_is_valid' => 'required',
            'location_is_valid' => 'required',
            'rut_match' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new solicitudes();
        $dataFound = solicitudes::findOrFail($id);

        $dataFound->applicant_at_home = $request->applicant_at_home;
        $dataFound->prevention_is_valid = $request->prevention_is_valid;
        $dataFound->location_is_valid = $request->prevention_is_valid;
        $dataFound->comments = $request->comments;
        $dataFound->rut_match = $request->rut_match;

        $dataFound->save();

        $response = solicitudes::query()->where(['id' => $id, 
        'applicant_at_home' => true, 
        'prevention_is_valid' => true,
        'location_is_valid' => true,
        'rut_match' => true
    ])->first();

        if (!$response) {
            solicitudes::query()->where(['id' => $id])->update(['id_status' => 7, 'is_active' => false]);
    
            $rut = DB::table('solicitudes')->where('id', $id)->value('rut_user');
            $email = DB::table('users')->where('rut', $rut)->value('email');

            try {    
                $mc = new MailController;

                $params = [
                    'id' => $id
                ];
        
                $ret = $mc->rejectRequest($email, $id, $params);
                if($ret !== true){
                    abort(500, $ret);
                }
                
            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new rechazarSolicitudMailable($id);
            //Mail::to($email)->send($send);

            return response()->json(['error' => 'Date '.$id.' has been rejected'], 400);
        }

        solicitudes::query()->where(['id' => $id])->update(['id_status' => 6]);

        $rut = DB::table('solicitudes')->where('id', $id)->value('rut_user');
        $email = DB::table('users')->where('rut', $rut)->value('email');

        try {
            $mc = new MailController;

            $params = [
                'id' => $id
            ];
    
            $ret = $mc->approve_Request($email, $id, $params);
            if($ret !== true){
                abort(500, $ret);
            }
        } catch (\Throwable $th) {
            return response()->json(['Error' => 'Failed to send Email'], 400);
        }

        return response()->json(['message' => 'Date '.$id. ' has been approved'], 200);
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
        $query_count = DB::select("SELECT COUNT(id) FROM consultants.solicitudes");
        $query_approveRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 6");
        $query_rejectRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 7");
        
         /* $query = DB::select(
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
            LEFT JOIN consultants.status as g ON a.id_status = g.id
            order by a.id ASC"
         ); */

        $query = DB::select(
            "SELECT 
            a.id as id_request,
            a.created_at as request_date, 
            a.rut_user,
            b.names as user_name,
            b.lastname as user_lastname,
            c.id as id_location,
            CASE WHEN b.origin = 'wots' THEN a.dir_wots ELSE c.street END AS street,
            CASE WHEN b.origin = 'wots' THEN '' ELSE a.way_number::varchar END AS way_number,
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
            a.id_status_emp, 
            g.status,
            h.status as status_emp
            FROM consultants.solicitudes as a 
            LEFT JOIN consultants.users as b ON a.rut_user = b.rut
            LEFT JOIN consultants.locations as c ON a.id_location = c.id
            LEFT JOIN consultants.property_types as d ON a.id_property_type = d.id
            LEFT JOIN consultants.users as e ON e.rut = a.rut_employee
            LEFT JOIN consultants.users as f ON f.rut = e.supervisor_rut
            LEFT JOIN consultants.status as g ON a.id_status = g.id
            LEFT JOIN consultants.status as h ON a.id_status_emp = h.id 
            order by a.id ASC"
         );
 
         return response()->json(
            [
                'count_solicitudes' => $query_count,
                'approve_Request' => $query_approveRequest,
                'reject_Request' => $query_rejectRequest,
                'data_complete' => $query
            ]);
     }

     public function guardCard($id_simulation)
     {
            $info = DB::select(
                "SELECT 
                a.rut_user,
                d.names,
                d.lastname,
                c.prevention_type,
                b.prevention_detail
                FROM consultants.solicitudes as a 
                JOIN consultants.preventions_details as b ON a.id_prevention_type_detail = b.id
                JOIN consultants.preventions_types as c ON b.id_prevention_type = c.id
                JOIN consultants.users as d ON a.rut_user = d.rut
                JOIN consultants.simulations as e ON a.id = e.id_request
                WHERE e.id = $id_simulation
                ");

                return response()->json($info);
     }
}
