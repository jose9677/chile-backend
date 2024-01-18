<?php

namespace App\Http\Controllers;

use App\Models\ModifyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ModifyUserController extends Controller
{
    public function allMoifyUser()
    {
        $data = ModifyUser::all();

        return response()->json($data, 200);
    }

    public function searchModifyUser($id_user)
    {
        $data = ModifyUser::findOrFail($id_user);
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function loadUserData(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'rut' => 'required',
            'names' => 'required',
            'lastname' => 'required',
            'f_nac' => 'required',
            'email' => 'required',
            'landline' => 'required',
            'mobile_phone' => 'required',
            'id_sex' => 'required',
            'rut_employee' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::where([
            'rut' => $request['rut']
        ])->get();

        if (!$user->count()) {
            return response()->json(['error' => 'User does not exists in DB'], 400);
        }

        $mod_user = ModifyUser::create([
            'rut' => Str::upper($request->get('rut')),
            'names' => Str::upper($request->get('names')),
            'lastname' => Str::upper($request->get('lastname')),
            'f_nac' => $request->get('f_nac'),
            'email' => Str::lower($request->get('email')),
            'landline' => $request->get('landline'),
            'mobile_phone' => $request->get('mobile_phone'),
            'id_sex' => $request->get('id_sex'),
            'rut_employee' => $request->get('rut_employee')
        ]);

        return response()->json([
            'user' => $mod_user,
            'appoved_by' => $this->DataModifer($request->rut_employee)
        ]);
    }

    public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $respnse = 'Aprobado por '.$names.' '.$lastname;

        return Str::upper($respnse);
    }
}
