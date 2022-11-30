<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\claveEmpleadoMailable;
use App\Mail\reenviarOTPMailable;
use App\Mail\registroMailable;
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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = new User();
        $solicitudes = new solicitudes();

        $user = $request->only([
            'rut',
            'names',
            'lastname',
            'f_nac',
            'email',
            'landline',
            'mobile_phone'
        ]);

        User::create($user);

        $solicitudes = $request->only([
            'id_location',
            'way_number',
            'id_property_type',
            'property_number',
            'id_prevention_type',
            'assurance'
        ]);

        solicitudes::create($solicitudes);

        $this->add_request();

        return response()->json([
            'user' => $user, 
            'request' => $solicitudes
        ], 200);
    }

    public function add_request()
    {
        $query = DB::insert(
                    
        "INSERT INTO consultants.users_requests
        SELECT 
        consultants.users.rut,
        MAX(consultants.solicitudes.id),
        consultants.users.supervisor_rut
        FROM
        consultants.users, consultants.solicitudes
        GROUP BY
        consultants.users.rut,
        consultants.users.supervisor_rut
        ORDER BY  consultants.users.created_at DESC LIMIT 1"
        );
    }
    
    public function registerEmployee(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            
            /*'password' => ['required',
                Password::min(8)
                ->letters() //al menos una letra
                ->mixedCase() //al menos una letra minuscula y una mayuscula
                ->numbers() //al menos un caracter numerico
            ],*/
            
            'landline' => 'required',
            'mobile_phone' => 'required',
            'supervisor_rut' => 'required',
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
            'id_rol' => $request->get('id_rol')
        ]);

        $send = new registroMailable($user->rut, $user->otp);
        Mail::to('josedarkrai@gmail.com')->send($send);

        $send_2 = new claveEmpleadoMailable($user->rut);
        Mail::to('josedarkrai@gmail.com')->send($send_2);

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
            'id_rol' => 1
        ]);

        $solicitudes = $request->only([
            'rut_user',
            'id_location',
            'way_number',
            'id_property_type_detail',
            'property_number',
            'id_prevention_type',
            'assurance',
            'visit_date'
        ]);

        solicitudes::create($solicitudes);

        $send = new registroMailable($user->rut, $user->otp);
        Mail::to('josedarkrai@gmail.com')->send($send);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'solicitudes', 'token'), 201); 
    }
    
    public function login(Request $request)
    {
        $data = new User();
        $data = User::query()->where(['rut' => $request->input('rut')])->first();

        if (!$data) {
            return response()->json(['message' => 'User does not exists in Data Base'], 400);
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

    public function refreshOtp($rut)
    {
        $user = User::find($rut);
        
        if (!$user) {
            return response()->json(['error' => 'User does not exists in Data Base'], 400);
        }

        if (!empty($user->otp)) {
            $otp = $this->generateCodOtp();
            $user->otp = $otp;

            $query = DB::update("UPDATE consultants.users SET otp = '$otp' WHERE rut = $rut");

            $send = new reenviarOTPMailable($user->otp);
            Mail::to('josedarkrai@gmail.com')->send($send);

            return response()->json(
                ['message' => 'OTP Updated',
                 'user_rut' => $user->rut
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
            User::query()->where('otp', $request->input('otp'))->update(['otp' => null, 'email_active' => true]);

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
