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

    public function approveUserChanges($rut)
    {
        /* $record = ModifyUser::where([
            'id_user' => $id_user
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        } */

        //$data = ModifyUser::query()->where('id_user', $id_user)->update(['approve' => true]);
        $data = ModifyUser::query()->where('rut', $rut)->update(['approve' => true]);
        
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
        
            $mod_user = ModifyUser::create([
                'rut' => Str::upper($rut),
                'names' => Str::upper($request->get('names')),
                'lastname' => Str::upper($request->get('lastname')),
                'f_nac' => $request->get('f_nac'),
                'email' => Str::lower($request->get('email')),
                'landline' => $request->get('landline'),
                'mobile_phone' => $request->get('mobile_phone'),
                'id_sex' => $request->get('id_sex'),
                'approve' => true
            ]);
        
        
        /* $gate = DB::table('modify_users')->where('rut', $rut)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        } */

        try {
            $update = DB::update(
                "UPDATE consultants.users 
                SET
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
            'message' => 'Record '.$rut.' updated successfull'
            //'approve_by' => $this->DataModifer($request->rut_employee)
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
