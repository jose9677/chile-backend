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
use App\Models\aps;
use App\Models\documents;
use App\Models\solicitudes;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'rut_user' => 'required',
            'names' => 'required',
            'lastname' => 'required',
            'id_sex' => 'required',
            'f_nac' => 'required',
            'email' => 'required',
            //'landline' => 'required',
            'mobile_phone' => 'required',
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'id_property_type' => 'required',
            'property_number' => 'required',
            'carer' => 'required',
            'id_motive' => 'required_if:carer,true',
            'id_dedication' => 'required_if:carer,true',
            'id_remuneration' => 'required_if:carer,true'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id_sol = DB::table('solicitudes')->where(['rut_user' => $request->rut_user, 'is_active' => true])->orderByDesc('id')->limit(1)->value('id');
        $id_status = DB::table('solicitudes')->where('id', $id_sol)->value('id_status');
        $status_rule = [7, 9, 14];

        $sol = new solicitudes();
        
        if (in_array($id_status, $status_rule) or empty($id_sol)) {
            $sol->rut_user = Str::upper($request->rut_user);
            $sol->id_prevention_type_detail = $request->get('id_prevention_type_detail');
            $sol->assurance = $request->get('assurance');
            $sol->id_location = $request->get('id_location');
            $sol->way_number = $request->get('way_number');
            $sol->id_property_type = $request->get('id_property_type');
            $sol->property_number = $request->get('property_number');
            $sol->terms_condition = $request->get('terms_condition');
            //$sol->request_date = time();
            $sol->id_status = 1;
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
            $sol->reg_type = 'REGISTRO POR PAGINA';
            $sol->origin = 'REGISTRO POR PAGINA';
            $sol->id_status_emp = 1;

            if ($request->terms_condition == false) {
                return response()->json(['error' => 'User must to accept terms condition'], 400);
            }
            elseif ($request->terms_condition == true) {
                $sol->save();
                $new_sol = DB::table('solicitudes')->where('rut_user', $sol->rut_user)->orderByDesc('id')->limit(1)->value('id');
                $this->percentRequets($new_sol);

            }
        }else {
            return response()->json(['error' => 'this user has a solicitude active'], 400);
        }

        try {
            $update_user = DB::table('users')->where('rut', $request->rut_user)->update([
                'names' => $request->names,
                'lastname' => $request->lastname,
                'id_sex' => $request->id_sex,
                'f_nac' => $request->f_nac,
                'email' => $request->email,
                'landline' => $request->landline,
                'mobile_phone' => $request->mobile_phone
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return response()->json(['sol' => $sol], 200);
    }

    public function percentRequets($id)
    {
        $rut_user = DB::table('solicitudes')->where('id', $id)->value('rut_user');
        
        $aps = DB::table('users')->where('rut', $rut_user)->value('aps');

        $prevention_detail = DB::table('preventions_details')
        ->select('prevention_detail')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where(['solicitudes.id' => $id, 'solicitudes.is_active' => true])
        ->value('prevention_detail');

        $prevention_type = DB::table('preventions_types')
        ->select('prevention_type')
        ->join('preventions_details', 'preventions_details.id_prevention_type', '=', 'preventions_types.id')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where(['solicitudes.id' => $id, 'solicitudes.is_active' => true])
        ->value('prevention_type');

        if ($prevention_type == 'FONASA' AND $prevention_detail == 'A') {
            solicitudes::query()->where('id', $id)->update(['percent' => 50]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == false) {
            solicitudes::query()->where('id', $id)->update(['percent' => 20]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == true) {
            $f_nac = DB::table('users')->where('rut', $rut_user)->value('f_nac');
            $age = Carbon::parse($f_nac)->age;

            if ($age < 60) {
                solicitudes::query()->where('id', $id)->update(['percent' => 30]);
            }
            elseif ($age >= 60) {
                solicitudes::query()->where('id', $id)->update(['percent' => 40]);
            }
        }

        if ($prevention_type != 'FONASA' AND $aps == false) {
            solicitudes::query()->where('id', $id)->update(['percent' => 20]);
        }

        if ($prevention_type != 'FONASA' AND $aps == true) {
            solicitudes::query()->where('id', $id)->update(['percent' => 20]);
        }

        return response()->json(['message' => 'All percents updated successfull'], 200);
    }

    public function confimRenoSol(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut_user' => 'required',
            'names' => 'required',
            'lastname' => 'required',
            'id_sex' => 'required',
            'f_nac' => 'required',
            'email' => 'required',
            //'landline' => 'required',
            'mobile_phone' => 'required',
            'id_prevention_type_detail' => 'required',
            'assurance' => 'required',
            'id_location' => 'required',
            'way_number' => 'required',
            'id_property_type' => 'required',
            'property_number' => 'required',
            'carer' => 'required',
            'id_motive' => 'required_if:carer,true',
            'id_dedication' => 'required_if:carer,true',
            'id_remuneration' => 'required_if:carer,true'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }  
        
        $update_user = DB::table('users')->where('rut', $request->rut_user)->update([
            'names' => $request->names,
            'lastname' => $request->lastname,
            'id_sex' => $request->id_sex,
            'f_nac' => $request->f_nac,
            'email' => $request->email,
            'landline' => $request->landline,
            'mobile_phone' => $request->mobile_phone
        ]);

        $update_sol = DB::table('solicitudes')->where('id', $id)->update([
            'id_prevention_type_detail' => $request->id_prevention_type_detail,
            'assurance' => $request->assurance,
            'id_location' => $request->id_location,
            'way_number' => $request->way_number,
            'id_property_type' => $request->id_property_type,
            'property_number' => $request->property_number,
            'carer' => $request->carer,
            'id_motive' => $request->id_motive,
            'id_dedication' => $request->id_dedication,
            'id_remuneration' => $request->id_remuneration
        ]);

        $update_status = DB::table('solicitudes')->where('id', $id)->update(['id_status' => 1, 'id_status_emp' => 1]);

        $this->percentRequets($id);

        return response()->json(['message' => 'Confirmation successfull'], 200);
    }

    public function consultRenoSol()
    {
        $id_sol = DB::table('solicitudes')->where(['is_active' => true, 'id_status' => 6])
                    ->orderByDesc('solicitudes.id')
                    ->get(['id', 'rut_user']);

        return $id_sol;
    }

    public function consultRenoDoc()
    {
        $id_doc = DB::table('solicitudes')
                    ->join('documents', 'documents.id_request', '=', 'solicitudes.id')
                    ->where(['solicitudes.is_active' => true, 'solicitudes.id_status' => 6, 'documents.active'=> true])
                    ->orderByDesc('documents.id')
                    ->get(['documents.id as id_doc']);

        return $id_doc;
    }

    public function renoveSol()
    {
        $data = $this->consultRenoSol();
        $data_doc = $this->consultRenoDoc();

        $count = count($data);
        $count_doc = count($data_doc);
        $id_sol = [];
        $rut_user = [];
        $id_doc = [];

        for ($i=0; $i < $count; $i++) { 
            $id_sol[] = $data[$i]->id;
            $rut_user[] = $data[$i]->rut_user;
        }

        for ($i=0; $i < $count_doc; $i++) { 
            $id_doc[] = $data_doc[$i]->id_doc;
        }

        for ($i=0; $i < $count; $i++) { 

            $reno_sol = DB::insert(
                "INSERT INTO consultants.solicitudes (
                rut_user, id_prevention_type_detail, assurance, id_location, way_number, id_property_type, property_number, terms_condition,
                request_date, id_status, rut_employee, visit_date, applicant_at_home, prevention_is_valid, location_is_valid, comments,
                is_active, rut_match, carer, id_motive, id_dedication, id_remuneration, created_at, updated_at, --admission_date, 
                dir_wots, latitude, longitude, reg_type, id_mod_status, id_status_emp, origin )
                SELECT 
                '$rut_user[$i]', id_prevention_type_detail, assurance, id_location, way_number, id_property_type, property_number, terms_condition,
                request_date, 13, rut_employee, visit_date, applicant_at_home, prevention_is_valid, location_is_valid, comments,
                is_active, rut_match, carer, id_motive, id_dedication, id_remuneration, created_at, updated_at, --admission_date,
                dir_wots, latitude, longitude, reg_type, id_mod_status, 13, origin
                FROM consultants.solicitudes WHERE id = $id_sol[$i];
                ");
    
            $update_status = DB::table('solicitudes')->where('id', $id_sol[$i])->update(['id_status' => 14, 'id_status_emp' => 14]);   
        }

        for ($i=0; $i < $count_doc; $i++) { 
            $reno_doc = DB::insert(
                "INSERT INTO consultants.documents(
                    id_request, id_document_type, document_path, document_ext, created_at, updated_at, active, id_mod_status)
                SELECT id_request, id_document_type, document_path, document_ext, created_at, updated_at, active, id_mod_status
                FROM consultants.documents WHERE id = $id_doc[$i]
            ");
        }

        return response()->json(['message' => 'renovation successfull']);
    }

    public function confimRenoSolAutoAprove($id)
    {
        $id_status = DB::table('solicitudes')->where('id', $id)->value('id_status');
        
        if ($id_status != 13) {
            return response()->json(['error' => 'This solicitude is not in renovatio status'], 400);
        }

        $update = DB::table('solicitudes')->where('id', $id)->update(['id_status' => 6]);

        return response()->json(['message' => 'Solicitude Apporved successfull'], 200);
    }

    public function solDoc(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document_path' => 'required',
            'rut_user' => 'required',
            'id_document_type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new documents();
        
        $query = solicitudes::where('rut_user', $request->rut_user)->max('id'); //ID ULTIMA SOLICITUD DEL USUARIO....
        $id_doc = documents::max('id') + 1;
        //$rut = DB::table('solicitudes')->where('id', $query)->value('rut_user');

        $data->id_request = $query;
        $data->id_document_type = $request->id_document_type;

        $documents_types = DB::table('documents_types')->where('id', $request->id_document_type)->value('document_type');

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$query.'-'.$id_doc.'.'.$extension;
 
            $path = $request->file('document_path')->storeAs('public/posts', $comPic);
            $data->document_path = $comPic;
            $data->document_ext = $extension;
            $data->active = true;
        }
        if ($data->save()) {
            documents::query()->where('id', $id_doc)->update(['active' => true]);

            return response()->json(['message' => 'Document '.$id_doc.' uploaded successfull'],200);
        }else {
            return response()->json(['error' => 'Error to upload document'],400);
        }
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
        $query_count = DB::select("SELECT COUNT(id) FROM consultants.solicitudes WHERE is_active = true");
        $query_approveRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 6 AND is_active = true");
        $query_rejectRequest = DB::select("SELECT * FROM consultants.solicitudes WHERE id_status = 7 AND is_active = true");

        $query = DB::select(
            "SELECT 
            a.id as id_request,
            a.created_at as request_date, 
            a.rut_user,
            b.names as user_name,
            b.lastname as user_lastname,
            c.id as id_location,
            CASE WHEN a.origin = 'wots' THEN a.dir_wots ELSE c.street END AS street,
            CASE WHEN a.origin = 'wots' THEN '' ELSE a.way_number::varchar END AS way_number,
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
            h.status as status_emp,
            a.percent
            FROM consultants.solicitudes as a 
            LEFT JOIN consultants.users as b ON a.rut_user = b.rut
            LEFT JOIN consultants.locations as c ON a.id_location = c.id
            LEFT JOIN consultants.property_types as d ON a.id_property_type = d.id
            LEFT JOIN consultants.users as e ON e.rut = a.rut_employee
            LEFT JOIN consultants.users as f ON f.rut = e.supervisor_rut
            LEFT JOIN consultants.status as g ON a.id_status = g.id
            LEFT JOIN consultants.status as h ON a.id_status_emp = h.id 
            WHERE a.is_active = true
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
