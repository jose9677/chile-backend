<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\claveEmpleadoMailable;
use App\Mail\reenviarOTPMailable;
use App\Mail\registroMailable;
use App\Mail\registroVecinoExitoso;
use App\Models\documents;
use App\Models\solicitudes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use App\Models\users_requests;
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
            'landline' => 'required',
            'mobile_phone' => 'required',
            //'supervisor_rut' => 'required',
            'profile_pic' => 'required',
            'id_rol' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
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
            'otp' => $this->generateCodOtp(),
            'email_active' => false,
            'profile_pic' => $request->get('profile_pic'),
            'id_rol' => $request->get('id_rol'), 
            'api_token' => Str::random(50),
            'active' => true
        ]);
        
        $send = new registroMailable($user->rut, $user->otp);
        Mail::to($request->email)->send($send);
        
        $api_token = DB::table('users')->where('rut', $request->rut)->value('api_token');
        $app_url = env('APP_URL');

        $send_2 = new claveEmpleadoMailable($user->rut, $api_token , $app_url);
        Mail::to($request->email)->send($send_2);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);   
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
            
            'landline' => 'required',
            'mobile_phone' => 'required',
            'carer' => 'required',
            'aps' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
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
            'aps' => $request->get('aps')
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
        $sol->is_active = false;
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

        $send = new registroMailable($user->otp);
        Mail::to($request->email)->send($send);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'sol', 'token'), 201); 
    }
    
    public function login(Request $request)
    {
        $data = new User();
        $data = User::query()->where(['rut' => $request->input('rut')])->first();

        if (!$data) {
            return response()->json(['error' => 'User does not exists in Data Base'], 400);
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

            return compact('success', 'message');
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

            $send = new reenviarOTPMailable($new_otp);
            Mail::to($email)->send($send);

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
            
            $send = new registroVecinoExitoso();
            Mail::to($email)->send($send);

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
            return response()->json(['error' => 'User exists in Data Base'], 400);
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
