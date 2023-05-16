<?php

namespace App\Http\Controllers;

use App\Models\cancel_motives;
use App\Models\dedications;
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
                    'error' => 'User registered incompleted in DB '], 400);
            }

            if ($email_active == true AND empty($otp)) {
                return response()->json(['error' => 'User exists in DB'], 400);
            }
        }
        
        if ($request->rut) {
            return response()->json(['message' => $request->rut.' Is Valid'], 200);
        }else {
            return response()->json(['error' => $request->rut.' Is not Valid'], 400);
        }
    }

    public function wotsRegisterNeighbor(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
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

        $user = User::create([
            'rut' => Str::upper($request->get('rut')),
            'names' => Str::upper($request->get('names')),
            'lastname' => Str::upper($request->get('lastname')),
            'f_nac' => $request->get('f_nac'),
            'email' => Str::lower($request->get('email')),
            'password' => Hash::make($this->generatePassword()),
            'landline' => $request->get('landline'),
            'mobile_phone' => $request->get('mobile_phone'),
            'employee' => false,
            //'otp' => $this->generateCodOtp(),
            'email_active' => false,
            'id_rol' => 1,
            'active' => true,
            //'aps' => false,
            'id_sex' => $request->get('id_sex')
        ]);

        $sol = new solicitudes();
        $sol->rut_user = Str::upper($request->rut);
        $sol->id_prevention_type_detail = $request->get('id_prevention_type_detail');
        $sol->assurance = $request->get('assurance');
        $sol->id_location = $request->get('id_location');
        $sol->way_number = $request->get('way_number');
        $sol->id_property_type = $request->get('id_property_type');
        $sol->property_number = $request->get('property_number');
        $sol->terms_condition = $request->get('terms_condition');
        $sol->id_status = 1;
        $sol->applicant_at_home = false;
        $sol->prevention_is_valid = false;
        $sol->location_is_valid = false;
        $sol->comments = $request->get('comments');
        $sol->is_active = true;
        $sol->rut_match = false;
        $sol->carer = $request->get('carer');
        $sol->id_motive = 1;
        $sol->id_dedication = 1;
        $sol->id_remuneration = 1;
        // $sol->admission_date = $request->get('admission_date');

        if ($request->terms_condition == false) {
            return response()->json(['error' => 'User must to accept terms condition'], 400);
        }
        elseif ($request->terms_condition == true) {
            $sol->save();
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

        $aps = Db::table('users')->where('rut', $request->rut)->value('aps');

        if ($prevention_type == 'FONASA' AND $prevention_detail == 'A') {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 50]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == false) {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 20]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == true) {
            $f_nac = DB::table('users')->where('rut', $request->rut)->value('f_nac');
            $age = Carbon::parse($f_nac)->age;

            if ($age < 65) {
                User::query()->where('rut', $request->input('rut'))->update(['percent' => 30]);
            }
            elseif ($age >= 65) {
                User::query()->where('rut', $request->input('rut'))->update(['percent' => 40]);
            }
        }

        if ($prevention_type != 'FONASA' AND $aps == false) {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 20]);
        }

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'sol', 'token'), 201); 
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

    public function wotsChangePassword(Request $request)
   {
        Log::info($request);
        $validator = Validator::make($request->all(), [    
            'password' => ['required',
                Password::min(8)
                ->letters() //al menos una letra
                ->mixedCase() //al menos una letra minuscula y una mayuscula
                ->numbers() //al menos un caracter numerico
            ],
            'api_token' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $data = User::where([
            'api_token' => $request['api_token']
        ])->get();

        if ($data->count()) {
            $data = new User();
            $data->password = Hash::make($request->password);
            //$rut = DB::table('users')->where('api_token', $request->input('api_token'))->value('rut');
            $email = DB::table('users')->where('api_token', $request->input('api_token'))->value('email');

            User::query()->where('api_token', $request->input('api_token'))->update(['api_token' => NULL, 'password' => $data->password]);
            
            try {    
                $mc = new MailController;
        
                $ret = $mc->newPassword($email);
                if($ret !== true){
                    abort(500, $ret);
                }
                
            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new cambiarclaveMailable();
            //Mail::to($email)->send($send);

            return response()->json(['message' => 'Password Changed successfull'], 200);
        }else {
            return response()->json(['error' => 'Invalid Token'], 400);
        }   
   }

   public function wotsValidateUser(Request $request)
   {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'rut' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = User::query()->where(['rut' => $request->input('rut')])->first();
        
        if (!$data) {
            return response()->json(['error' => 'User Does Not exists in Data Base'], 400);
        }

        $data = User::where([
            'rut' => $request['rut'],
        ])->get();

        if ($data->count()) {
    
            $email_active = DB::table('users')->where('rut', $request->rut)->value('email_active');
            $otp = DB::table('users')->where('rut', $request->rut)->value('otp');

            if ($email_active == false AND !empty($otp)) {
                $this->restartUser($request->rut);
                return response()->json(['error' => 'the user '.$request->rut.' was deleted'], 400);
            
            }
            elseif($email_active == true AND empty($otp)){
            $api_token = $this->getNewToken();
            $data->api_token = $api_token;
            
            User::query()->where('rut', $request->input('rut'))->update(['api_token' => $api_token]);
            
            $email = DB::table('users')->where('rut', $request->input('rut'))->value('email');
            $app_url = env('APP_URL');
            
            try {    
                $mc = new MailController;

                $params = [
                    'api_token' => $data->api_token,
                    'app_url' => $app_url
                ];
        
                $ret = $mc->validate_User($email, $params);
                if($ret !== true){
                    abort(500, $ret);
                }

            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new validarMailable($data->api_token, $app_url);
            //Mail::to($email)->send($send);

            return response()->json(
                [
                    'message' => 'User Validation successfull',
                    'api_token' => $api_token
                ],  200);
            }   
        }
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
            return response()->json(['error' => 'Email exist into DB'], 400);
        }

        return response()->json(['message' => 'Email does not registered'], 200);
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

    public function wotsAuth(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'user' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = DB::table('wots_token')->where('user', $request->user)->value('user');
        $password = DB::table('wots_token')->where('password', $request->password)->value('password');

        if ($request->user === $user AND $request->password === $password) {
            $token = $this->getNewToken();
            wots_token::query()->where('user', $request->user)->update(['api_token' => $token]);
            
            return response()->json(
                ['message' => 'User autenticated',
                 'api_token' => $token
                ], 200);
        }
        else {
            return response()->json(['error' => 'User or Password Incorrect'], 400);
        }
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

    public function wotsOut(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'api_token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $api_token = DB::table('wots_token')->where('api_token', $request->api_token)->value('api_token');

        if ($request->api_token === $api_token) {
            wots_token::query()->where('api_token', $request->api_token)->update(['api_token' => null]);

            return response()->json(['message' => 'User Logout'], 200);
        }
        else {
            return response()->json(['error' => 'api_token Does not exists'], 400);
        }
    }
}
