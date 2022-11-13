<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class usersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        'mobile_phone' => 'required'
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

    try {
        $data->save();
    } catch (\Throwable $th) {
        return response()->json(['Update Failed'], 400);
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
            return response()->json(['User Does not exist in Data Base'], 400);
        }

        return response()->json(['message' => 'User '.$data->rut.' was deleted successfull'], 200);
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
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $data = User::where([
            'rut' => $request['rut']
        ])->get();

        if ($data->count()) {
            $data = new User();
            $data->password = Hash::make($request->password);
            
            User::query()->where('rut', $request->input('rut'))->update(['password' => $data->password]);

            return response()->json(['message' => 'Password Changed successfull'], 200);
        }else {
            return response()->json(['error' => 'Rut does not exists in Data Base'], 400);
        }   
   }
}
