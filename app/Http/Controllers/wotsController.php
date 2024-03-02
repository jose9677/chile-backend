<?php

namespace App\Http\Controllers;

use App\Models\cancel_motives;
use App\Models\dedications;
use App\Models\documents_types;
use App\Models\locations;
use App\Models\motives;
use App\Models\preventions_details;
use App\Models\preventions_types;
use App\Models\property_types;
use App\Models\remunerations;
use App\Models\sex;
use App\Models\solicitudes;
use App\Models\status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Malahierba\ChileRut\ChileRut;
use Malahierba\ChileRut\Facades\ChileRut as FacadesChileRut;
use Malahierba\ChileRut\Rules\ValidChileanRut;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\wots_token;

use Illuminate\Support\Facades\Auth;

class wotsController extends Controller
{
    
    public function validateRut(Request $request)
    {
        $request->validate([
            'rut' => ['required', 'string', new ValidChileanRut(new ChileRut)],
        ]);

        $user = User::where([
            'rut' => Str::upper($request['rut']) 
        ])->get();

        if ($user->count()) {
            
            $email_active = DB::table('users')->where('rut', $request->rut)->value('email_active');
            $otp = DB::table('users')->where('rut', $request->rut)->value('otp');
            
            if ($email_active == false AND !empty($otp)) {
                $restart = new usersController();
                $restart->restartUser($request->rut);        
                
                return response()->json([
                    'error' => 'User is not registered'], 400);
            }

            if ($email_active == true AND empty($otp)) {
                return response()->json(['error' => 'User exists in DB'], 400);
            }
        }
        
        /* if ($request->rut) {
            //return response()->json(['message' => 'User is not registered'], 200);
            return response()->json(['message' => $request->rut.' Is Valid'], 200);
        }else {
            return response()->json(['error' => $request->rut.' Is not Valid'], 400);
        } */
    }

    public function wotsRegisterNeighbor(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'email' => 'required|string|email|max:255',
            /* 
            'password' => ['required',
                Password::min(8)
                ->letters() //al menos una letra
                ->mixedCase() //al menos una letra minuscula y una mayuscula
                ->numbers() //al menos un caracter numerico
            ], */
            'mobile_phone' => 'required',
            'id_sex' => 'required',
            //'id_prevention_type_detail' => 'required',
            'carer' => 'required',
            // 'admission_date' => 'required'
            'dir_wots' => 'required',
            'latitude' => 'required',
            'longitude'   => 'required',
            'url_residence_file' => 'required',
            'url_prevition_file' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where([
            'rut' => $request['rut']
        ])->get();

        if ($user->count()) {
            return response()->json(['error' => 'User exists in DB'], 400);
        }

        $verify = $this->validateRut($request);

        if ($verify) {
            return response()->json(['error' => 'Invalid Rut'], 400);
        }

        $user = new User();
        $user->rut = Str::upper($request->get('rut'));
        $user->names = Str::upper($request->get('names'));
        $user->lastname = Str::upper($request->get('lastname'));
        $user->f_nac = $request->get('f_nac');
        $user->email = Str::lower($request->get('email'));
        $user->password = Hash::make($this->generatePassword());
        $user->landline = $request->get('landline');
        $user->mobile_phone = $request->get('mobile_phone');
        $user->employee = false;
        $user->email_active = true;
        $user->id_rol = 1;
        $user->active = true;
        $user->aps = false;
        $user->id_sex = $request->get('id_sex');
        $user->origin = 'wots';

        $sol = new solicitudes();
        $sol->rut_user = Str::upper($request->rut);
        $sol->id_prevention_type_detail = $request->get('id_prevention_type_detail');
        $sol->assurance = $request->get('assurance');
        $sol->id_location = 1;
        $sol->way_number = 1;
        $sol->id_property_type = $request->get('id_property_type');
        $sol->property_number = $request->get('property_number');
        $sol->terms_condition = $request->get('terms_condition');
        $sol->id_status = 1;
        $sol->applicant_at_home = false;
        $sol->prevention_is_valid = false;
        $sol->location_is_valid = false;
        $sol->comments = null;
        $sol->is_active = true;
        $sol->rut_match = false;
        $sol->carer = $request->get('carer');
        $sol->id_motive = $request->get('id_motive');
        $sol->id_dedication = $request->get('id_dedication');
        $sol->id_remuneration = $request->get('id_remuneration');
        // $sol->admission_date = $request->get('admission_date');
        $sol->dir_wots = $request->get('dir_wots');
        $sol->latitude = $request->get('latitude');
        $sol->longitude = $request->get('longitude');
        $sol->reg_type = 'wots';
        $sol->id_status_emp = 1;
        $sol->origin = 'wots';
        
        if ($request->terms_condition == true) {
            $user->save();
            $sol->save();
        }
        elseif ($request->terms_condition == false) {
            $data_user = DB::delete("DELETE FROM consultants.users WHERE rut = '$request->rut");
            $data_sol = DB::delete("DELETE FROM consultants.solicitudes WHERE rut = '$request->rut");

            return response()->json(['error' => 'User must to accept terms condition'], 400);
        }

        $file = new documentsController();
        
        $url_1 = $file->urlFile($request->url_residence_file,$user->rut, 1);//This Url make reference to residence file---
        $url_2 = $file->urlFile($request->url_prevition_file,$user->rut, 2);//This Url make reference to prevition file---
        $gate = false;

        if ($url_1 AND $url_2) {
            $gate = true;
        }
        
        if ($gate == false) {
            $data_user = DB::delete("DELETE FROM consultants.users WHERE rut = '$request->rut");
            $data_sol = DB::delete("DELETE FROM consultants.solicitudes WHERE rut = '$request->rut");
            
            return response()->json(['error' => 'User must to load files'], 400);
        }

        $verify_rut = DB::table('aps')->where('rut', $request->rut)->value('rut');
        
        if ($verify_rut) {
            User::query()->where('rut', $request->rut)->update(['aps' => true]);
        }elseif ($verify_rut == null) {
            User::query()->where('rut', $request->rut)->update(['aps' => false]);
        }

        $prevention_detail = DB::table('preventions_details')
        ->select('prevention_detail')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $request->rut)
        ->value('prevention_detail');

        $prevention_type = DB::table('preventions_types')
        ->select('prevention_type')
        ->join('preventions_details', 'preventions_details.id_prevention_type', '=', 'preventions_types.id')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $request->rut)
        ->value('prevention_type');

        
        $token = JWTAuth::fromUser($user);
        return response()->json(['message' => 'User registered'], 200);
    }

    public function generatePassword()
    {
        $lenght = 8;

        $characters = '0123456789#$%&/=-+_.';

        $charactersLenght = strlen($characters);

        $randomString = '';

        for ($i=0; $i < $lenght; $i++) { 
            $randomString .= $characters[rand(0, $charactersLenght -1)];
        }

        return $randomString;
    }

    public function wotsConsultEmail(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = User::where([
            'email' => $request['email']
        ])->get();

        if ($data->count()) {
            return response()->json(['error' => 'Email already exist into DB'], 400);
        }

        return response()->json(['message' => 'Email does not exist into DB'], 200);
   }

   public function wotsDropdownInfo()
    {
        $status = status::all();
        $property_types = property_types::all();
        $preventions_types_details = preventions_details::all();
        $preventions_types = preventions_types::all();
        $locations = locations::all();
        $motives = motives::all();
        $dedications = dedications::all();
        $remunerations = remunerations::all();
        $sex = sex::all();

        return response()->json([
            'status' => $status,
            'property_types' => $property_types,
            'preventions_types_details' => $preventions_types_details,
            'preventions_types' => $preventions_types,
            'locations' => $locations,
            'motives' => $motives,
            'dedications' => $dedications,
            'remunerations' => $remunerations,
            'sex' => $sex
        ]);
    }

    public function getNewToken()
    {
        $newToken = Str::random(50);
        return $newToken;
    }

    public function wotsAuthJWT(Request $request)
    {
        $data = new wots_token();
        //$data = wots_token::query()->where(['user', $request->input('user')])->first();
        $data = DB::table('wots_token')->where('user', $request->user)->value('user');

        if (!$data) {
            return response()->json(['error' => 'User dows not exists in Data Base']);
        }

        $credentials = $request->only(['user', 'password']);

        if (Auth::guard('api')->attempt($credentials)) {
            $user = Auth::guard('api')->user();
            $jwt = JWTAuth::attemp($credentials);
            $success = true;
            $api_token = $this->getNewToken();
            wots_token::query()->where('user', $request->user)->update(['api_token' => $api_token]);
            $data = compact('user', 'jwt');

            return compact('success', 'data');
        }else {
            $success = false;
            $message = 'User or Password Incorrect';

            return response()->json(['error' => $message], 400);
        }
    }
    
    public function wotsAuth(Request $request)
    {
        $data = new User();
        $data = User::query()->where(['rut' => $request->input('rut')])->first();

        if (!$data) {
            return response()->json(['error' => 'User does not exists in Data Base'], 400);
        }
        
        if ($data->email_active == false) {
            return response()->json(['error' => 'Inactive Email'], 400);
        }
        
        //this validation ask if the user is a employee, in that case the method must to ask if
        //that employee is active or not.
        if ($data->employee == true) {
            $data = User::query()->where(['rut' => $request->input('rut'), 'active' => true])->first();
                if (!$data) {
                    return response()->json(['error' => 'This employee is not actived'], 400);
                }
        }

        //$data = DB::select("SELECT rut FROM consultants.users WHERE rut In (SELECT user FROM consultants.wots_token)");
        $data = DB::select(
            "SELECT 
            a.rut 
            FROM consultants.users as a
            JOIN consultants.wots_token as b ON a.rut = b.user"
        );
        
        if ($data) {
            $credentials = $request->only(['rut', 'password']);

            if (Auth::guard('api')->attempt($credentials)) {
                $user = Auth::guard('api')->user();
                $jwt = JWTAuth::attempt($credentials);
                $success = true;
                $api_token = Str::random(50);
                wots_token::query()->where('user', $request->input('rut'))->update(['api_token' => $api_token]);
                User::query()->where('rut', $request->input('rut'))->update(['origin' => 'wots']);
                $data = compact('jwt');

                return compact('success', 'data');
            }else {
                $success = false;
                $message = 'User or Password Incorrect';
                return response()->json(['error' => $message], 400);
            }
        }
        elseif (!$data) {
            $credentials = $request->only(['rut', 'password']);

            if (Auth::guard('api')->attempt($credentials)) {
                $user = Auth::guard('api')->user();
                $jwt = JWTAuth::attempt($credentials);
                $success = true;
                $data = compact('jwt');

                return compact('success', 'data');
            }else {
                $success = false;
                $message = 'User or Password Incorrect';
                return response()->json(['error' => $message], 400);
            }
        }
    }

    public function wotsOut()
    {
        Auth::guard('api')->logout();
        $success = true;

        return compact('success');
    }

    public function consultRenoSol()
    {
        $id_sol = DB::table('solicitudes')->where(['is_active' => true, 'id_status' => 6])
                    ->orderByDesc('id')
                    ->get(['id', 'rut_user']);

        return $id_sol;
    }

    public function wotsRenoveSol()
    {
        $data = $this->consultRenoSol();

        $count = count($data);
        $id_sol = [];
        $rut_user = [];

        for ($i=0; $i < $count; $i++) { 
            $id_sol[] = $data[$i]->id;
            $rut_user[] = $data[$i]->rut_user;
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

            $id_reno_sol = DB::table('solicitudes')->where(['is_active' => true, 'id_status' => 13, 'rut_user' => $rut_user[$i]])->value('id');;
            
            $reno_doc = DB::insert(
                "INSERT INTO consultants.documents(
                    id_request, id_document_type, document_path, document_ext, created_at, updated_at, active, id_mod_status)
                SELECT $id_reno_sol, id_document_type, document_path, document_ext, created_at, updated_at, active, id_mod_status
                FROM consultants.documents WHERE id_request = $id_sol[$i] AND active = true
            ");
        }

        return response()->json(['message' => 'renovation successfull']);
    }
}
