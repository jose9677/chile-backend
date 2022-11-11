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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
           'name'  => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'password' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::guard('api')->attempt($credentials)) {
            $user = Auth::guard('api')->user();
            $jwt = JWTAuth::attempt($credentials);
            $success = true;
            $data = compact('user', 'jwt');

            return compact('success', 'data');
        }else {
            $success = false;
            $message = 'Failed Validation';

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
}
