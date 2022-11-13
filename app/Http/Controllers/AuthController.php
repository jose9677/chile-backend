<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
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
            'id_way' => 'required',
            'way_name' => 'required',
            'number_way' => 'required',
            'id_property' => 'required',
            'name_num_property' => 'required'
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
            'id_way' => $request->get('id_way'),
            'way_name' => $request->get('way_name'),
            'number_way' => $request->get('number_way'),
            'id_property' => $request->get('id_property'),
            'name_num_property'=> $request->get('name_num_property'),
            'otp' => $this->generateCodOtp()
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);
        
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
            User::query()->where('otp', $request->input('otp'))->update(['otp' => null]);

            return response()->json(['message' => 'Email validated succesfull'], 200);
        }else {
            return response()->json(['error' => 'Invalid code, please check it again'], 400);
        }
    }
}
