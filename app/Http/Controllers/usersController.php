<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\cambiarclaveMailable;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

use App\Mail\ContactanosMailable;
use App\Mail\validarMailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class usersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getNewToken()
    {
        $newToken = Str::random(50);
        return $newToken;
    }

    public function detailsUser()
    {
        $user = User::all();
        return response()->json($user);
    }

   public function search($rut)
   {
        $data = new User();
        $datafound = $data->findOrFail($rut);

        return response()->json([$datafound], 200);
   }

   public function employees()
   {
        $query = DB::select(
            "SELECT * FROM consultants.users WHERE employee = true"
        );

        return response()->json(['data' => $query], 200);
   }

   public function update(Request $request, $rut)
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
        'employee' => 'required',
        'supervisor_rut' => 'required',
        'profile_pic' => 'required'

    ]);
    
    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }

    $data = new User();
    $data = User::findOrFail($rut);

    $data->rut = $request->rut;
    $data->names = $request->names;
    $data->lastname = $request->lastname;
    $data->f_nac = $request->f_nac;
    $data->email = $request->email;
    $data->landline = $request->landline;
    $data->mobile_phone = $request->mobile_phone;
    $data->employee = $request->employee;
    $data->supervisor_rut = $request->supervisor_rut;
    $data->profile_pic = $request->profile_pic;

    try {
        $data->save();
    } catch (\Throwable $th) {
        return response()->json(['error' => 'Update Failed'], 400);
    }

    return response()->json([
        'message' => 'User Updated successfull',
        'data_user' => $data->rut
    ], 200);
   }

   public function delete($rut)
   {
        $data = User::find($rut);

        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'User Does not exist in Data Base'], 400);
        }

        return response()->json(['message' => 'User '.$data->rut.' was deleted successfull'], 200);
   }

   public function validateUser(Request $request)
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
            $data = User::query()->where(['rut' => $request->input('rut'),'email_active' => true])->first();

            if (!$data) {
                return response()->json(['error' => 'Inactive Email'], 400);
            }

            $api_token = $this->getNewToken();
            $data->api_token = $api_token;

            User::query()->where('rut', $request->input('rut'))->update(['api_token' => $api_token]);

            $send = new validarMailable($data->api_token);
            Mail::to('testing@angulo-group.com')->send($send);

            return response()->json(
                [
                    'message' => 'User Validation successfull',
                    'api_token' => $api_token
                ],  200);
        }
        else {
            return response()->json(['error' => 'User Does Not exists in Data Base'], 400);
        }
   }

   public function changePassword(Request $request)
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
            
            User::query()->where('api_token', $request->input('api_token'))->update(['api_token' => NULL, 'password' => $data->password]);

            $send = new cambiarclaveMailable($data); // CORREGIR NO IMPRIME EL RUT DEL USUARIO...
            Mail::to('testing@angulo-group.com')->send($send);

            return response()->json(['message' => 'Password Changed successfull'], 200);
        }else {
            return response()->json(['error' => 'Invalid Token'], 400);
        }   
   }
}
