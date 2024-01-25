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
            'id_sex' => 'required'
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
            'approve' => false
        ]);

        return response()->json([
            'user' => $mod_user
        ]);
    }

    public function approveUserChanges($id_user, $rut)
    {
        $record = ModifyUser::where([
            'id_user' => $id_user
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyUser::query()->where('id_user', $id_user)->update(['approve' => true]);
        
        return response()->json([
            'response' => 'Changes approved',
            'approved_by' => $this->DataModifer($rut)
        ]);
    }

    public function rejectUserChanges($id_user, $rut)
    {
        $record = ModifyUser::where([
            'id_user' => $id_user
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyUser::query()->where('id_user', $id_user)->update(['approve' => false]);
        
        return response()->json([
            'response' => 'Changes approved',
            'rejected_by' => $this->DataModifer($rut)
        ]);
    }

    public function updateUserData(Request $request, $rut)
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

        $data = ModifyUser::where([
            'rut' => $rut
        ])->get();

        if (!$data->count()) {
            return response()->json(['error' => 'This modify is not exists'], 400);
        }

        $gate = DB::table('modify_users')->where('rut', $rut)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        }

        try {
            $update = DB::update(
                "UPDATE consultants.users 
                SET
                rut = '$request->rut',
                names = '$request->names',
                lastname = '$request->lastname',
                f_nac = '$request->f_nac',
                email = '$request->email',
                landline = $request->landline,
                mobile_phone = $request->mobile_phone,
                id_sex = $request->id_sex
                WHERE rut = '$request->rut'");    
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Fail to update user data'], 400);
        } 
        
        return response()->json([
            'message' => 'Record '.$rut.' updated successfull',
            'approve_by' => $this->DataModifer($request->rut_employee)
        ]);
    }

    public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    }
}
