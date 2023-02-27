<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\claveEmpleadoMailable;
use App\Mail\reenviarOTPMailable;
use App\Mail\registroMailable;
use App\Mail\registroVecinoExitoso;
use App\Models\aps;
use App\Models\documents;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use App\Models\users_requests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use Malahierba\ChileRut\ChileRut;
use Malahierba\ChileRut\Facades\ChileRut as FacadesChileRut;
use Malahierba\ChileRut\Rules\ValidChileanRut;
use Symfony\Component\Console\Input\Input;

use Illuminate\Support\Str;

class AuthController extends Controller
{  
    public function registerEmployee(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            //'landline' => 'required',
            'mobile_phone' => 'required',
            //'supervisor_rut' => 'required',
            //'profile_pic' => 'required',
            'id_rol' => 'required',
            'id_sex' => 'required'
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
            'rut' => $request->get('rut'),
            'names' => $request->get('names'),
            'lastname' => $request->get('lastname'),
            'f_nac' => $request->get('f_nac'),
            'email' => $request->get('email'),
            'password' => Hash::make($this->employeePassword()),
            'landline' => $request->get('landline'),
            'mobile_phone' => $request->get('mobile_phone'),
            'employee' => true,
            'supervisor_rut' => $request->get('supervisor_rut'),
            //'otp' => $this->generateCodOtp(),
            'email_active' => true,
            //'profile_pic' => $request->get('profile_pic'),
            'id_rol' => $request->get('id_rol'), 
            'api_token' => Str::random(50),
            'active' => true,
            'id_sex' => $request->get('id_sex')
        ]);

        /*$mc = new MailController;
        $params = [
            'otp' => $user->otp,
            'url' => env('APP_URL')
        ];

        $ret = $mc->register($request->get('email'), $params);
        if($ret !== true){
            abort(500, $ret);
        }*/
        
        //$send = new registroMailable($user->rut, $user->otp);
        //Mail::to($request->email)->send($send);
        
        /*$api_token = DB::table('users')->where('rut', $request->rut)->value('api_token');
        $app_url = env('APP_URL');

        $mc = new MailController;
        $params = [
            'rut' => $user->rut,
            'api_token' => $api_token,
            'app_url' => $app_url,
        ];

        $ret = $mc->passwordEmployee($request->get('email'), $params);
        if($ret !== true){
            abort(500, $ret);
        }*/

        //$send_2 = new claveEmpleadoMailable($user->rut, $api_token , $app_url);
        //Mail::to($request->email)->send($send_2);
        
        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);   
    }

    public function profilePic(Request $request, $rut)
    {
        if ($request->hasFile('profile_pic')) {
            $completeFilename = $request->file('profile_pic')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('profile_pic')->getClientOriginalExtension();

            $comPic = str_replace(' ', '_', $request->rut).'.'.$extension;
        
            $path = $request->file('profile_pic')->storeAs('public/profile_pic', $comPic);
            $pic = User::query()->where('rut', $rut)->update(['profile_pic' => $comPic]);
        }       

        return response()->json(['message' => 'Pic uploaded successfull'],200);
    }

    public function employeePassword()
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

    public function registerNeighbor(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            
            'password' => ['required',
                Password::min(8)
                ->letters() //al menos una letra
                ->mixedCase() //al menos una letra minuscula y una mayuscula
                ->numbers() //al menos un caracter numerico
            ],
            'mobile_phone' => 'required',
            'id_sex' => 'required',
            'id_prevention_type_detail' => 'required',
            'carer' => 'required',
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
            'rut' => $request->get('rut'),
            'names' => $request->get('names'),
            'lastname' => $request->get('lastname'),
            'f_nac' => $request->get('f_nac'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'landline' => $request->get('landline'),
            'mobile_phone' => $request->get('mobile_phone'),
            'employee' => false,
            'otp' => $this->generateCodOtp(),
            'email_active' => false,
            'id_rol' => 1,
            'active' => true,
            'aps' => $request->get('aps'),
            'id_sex' => $request->get('id_sex')
        ]);

        $sol = new solicitudes();
        $sol->rut_user = $request->rut;
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
        $sol->id_motive = $request->get('id_motive');
        $sol->id_dedication = $request->get('id_dedication');
        $sol->id_remuneration = $request->get('id_remuneration');

        if ($request->terms_condition == false) {
            return response()->json(['error' => 'User must to accept terms condition'], 400);
        }
        elseif ($request->terms_condition == true) {
            $sol->save();
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

        if ($prevention_type == 'FONASA' AND $prevention_detail == 'A') {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 50]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $request->aps == false) {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 20]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $request->aps == true) {
            $f_nac = DB::table('users')->where('rut', $request->rut)->value('f_nac');
            $age = Carbon::parse($f_nac)->age;

            if ($age < 65) {
                User::query()->where('rut', $request->input('rut'))->update(['percent' => 30]);
            }
            elseif ($age >= 65) {
                User::query()->where('rut', $request->input('rut'))->update(['percent' => 40]);
            }
        }

        if ($prevention_type != 'FONASA' AND $request->aps == false) {
            User::query()->where('rut', $request->input('rut'))->update(['percent' => 20]);
        }

        $mc = new MailController;
        $params = [
            'otp' => $user->otp
        ];

        $ret = $mc->register($request->get('email'), $params);
        if($ret !== true){
            abort(500, $ret);
        }

        //$send = new registroMailable($user->otp);
        //Mail::to($request->email)->send($send);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'sol', 'token'), 201); 
    }

    public function updateAps(Request $request)
    {
        $sweep_aps = DB::update("UPDATE consultants.users SET aps = false");
        
        $query = DB::update(
            "UPDATE consultants.users
             SET aps = true 
             WHERE rut in (SELECT rut FROM consultants.aps)"
        );
        
        //WE GET ALL RECORDS OF USERS TABLE
        $rut_users = DB::table('users')->select('rut')->where('employee', false)->get();
        
        //ESTE METODO DA ERROR DE TIEMPO DE EJECUCION A LA HORA DE ACTUALIZAR TODOS LOS PORCENTAJES
        //DE CADA RESULTADO DE LA TABLA USUARIOS, SI LE PONGO UN LIMITE DE 10 SI FUNCIONA....

        $ruts = [];
        foreach ($rut_users as $all_rut) {
            $ruts[] = $all_rut->rut;
        }

        for ($i=0; $i <count($ruts) ; $i++) { 
        
        $aps = DB::table('users')->where('rut', $ruts[$i])->value('aps');

        $prevention_detail = DB::table('preventions_details')
        ->select('prevention_detail')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $ruts[$i])
        ->value('prevention_detail');

        $prevention_type = DB::table('preventions_types')
        ->select('prevention_type')
        ->join('preventions_details', 'preventions_details.id_prevention_type', '=', 'preventions_types.id')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $ruts[$i])
        ->value('prevention_type');

        if ($prevention_type == 'FONASA' AND $prevention_detail == 'A') {
            User::query()->where('rut', $ruts[$i])->update(['percent' => 50]);
        }

        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == false) {
            User::query()->where('rut', $ruts[$i])->update(['percent' => 20]);
        }

        //ESTA CONDICION TENIA APS TRUE ANTERIORMENTE...
        if ($prevention_type == 'FONASA' AND $prevention_detail != 'A' AND $aps == false) {
            $f_nac = DB::table('users')->where('rut', $ruts[$i])->value('f_nac');
            $age = Carbon::parse($f_nac)->age;

            if ($age < 65) {
                User::query()->where('rut', $ruts[$i])->update(['percent' => 30]);
            }
            elseif ($age >= 65) {
                User::query()->where('rut', $ruts[$i])->update(['percent' => 40]);
            }
        }

        if ($prevention_type != 'FONASA' AND $aps == false) {
            User::query()->where('rut', $ruts[$i])->update(['percent' => 20]);
            }
        }

        return response()->json(['message' => 'All percents updated successfull'], 200);
    }
    
    public function login(Request $request)
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

        $credentials = $request->only(['rut', 'password']);

        if (Auth::guard('api')->attempt($credentials)) {
            $user = Auth::guard('api')->user();
            $jwt = JWTAuth::attempt($credentials);
            $success = true;
            $data = compact('user', 'jwt');

            return compact('success', 'data');
        }else {
            $success = false;
            $message = 'User or Password Incorrect';

            //return compact('success', 'message');
            return response()->json(['error' => $message], 400);
        }
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        $success = true;

        return compact('success');
    }

    public function generateCodOtp()
    {
        $lenght = 6;

        $characters = '0123456789';

        $charactersLenght = strlen($characters);

        $randomString = '';

        for ($i=0; $i < $lenght; $i++) { 
            $randomString .= $characters[rand(0, $charactersLenght -1)];
        }

        return $randomString;
    }

    //This method generate a new OTP in case needed...
    public function refreshOtp(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where([
            'rut' => $request['rut']
        ])->get();
    
        if (!$user->count()) {
            return response()->json(['error' => 'User does not exists in Data Base'], 400);
        }

        $otp = DB::table('users')->where('rut', $request->input('rut'))->value('otp');

        if (!empty($otp)) {
            $new_otp = $this->generateCodOtp();

            $query = DB::update("UPDATE consultants.users SET otp = '$new_otp' WHERE rut = '$request->rut'");
            $email = DB::table('users')->where('rut', $request->input('rut'))->value('email');

            $mc = new MailController;
            $params = [
                'otp' => $new_otp
            ];
    
            $ret = $mc->newOTP($new_otp ,$email, $params);
            if($ret !== true){
                abort(500, $ret);
            }

            //$send = new reenviarOTPMailable($new_otp);
            //Mail::to($email)->send($send);

            return response()->json(
                ['message' => 'OTP Updated',
                 'new_otp' => $new_otp,
                 'user_rut' => $request->rut
                ], 200);
        }else {
            return response()->json(['error' => 'This user does not need a OTP'], 400);
        }
    }

    public function validateEmail(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'otp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $data = User::where([
            'otp' => $request['otp']
        ])->get();

        if ($data->count()) {
            $email = DB::table('users')->where('otp', $request->input('otp'))->value('email');
            User::query()->where('otp', $request->input('otp'))->update(['otp' => null, 'email_active' => true]);
            
            $mc = new MailController;
    
            $ret = $mc->registerNeighbor($email);
            if($ret !== true){
                abort(500, $ret);
            }
            
            //$send = new registroVecinoExitoso();
            //Mail::to($email)->send($send);

            return response()->json(['message' => 'Email validated succesfull'], 200);
        }else {
            return response()->json(['error' => 'Invalid code, please check it again'], 400);
        }
    }

    public function validateRut(Request $request)
    {
        $request->validate([
            'rut' => ['required', 'string', new ValidChileanRut(new ChileRut)],
        ]);

        $user = User::where([
            'rut' => $request['rut']
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

    public function test(Request $request)
    {
        $digitoVerificador = FacadesChileRut::digitoVerificador($request->rut);

        return response()->json($digitoVerificador);
    }
}
