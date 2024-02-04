<?php

namespace App\Http\Controllers;

use App\Models\ModifyUser;
use App\Models\User;
use Exception;
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

    public function compUserData(Request $request)
    {
        $data = DB::select(
            "SELECT 
            a.rut,
            case when a.names = $request->names then true else false end as comp_names,
            case when a.lastname = $request->lastname then true else false end as comp_lastname,
            case when a.id_sex = $request->id_sex then true else false end as comp_sex,
            case when a.f_nac = $request->f_nac then true else false end as comp_f_nac,
            case when a.email = $request->email then true else false end as comp_email,
            case when a.landline = $request->landline then true else false end as comp_landline,
            case when a.mobile_phone = $request->mobile_phone then true else false end as comp_mobile
            FROM consultants.users as a 
            JOIN consultants.modify_users as b USING (rut)
            WHERE a.rut = '$request->rut'
            ");

            return response()->json($data, 200);
    }

    public function updateUserData(Request $request)
    {
        $id_user = DB::table('modify_users')->where('rut', $request->rut)->orderByDesc('id_user')->limit(1)->value('id_user');

        $compare = DB::select(
            "SELECT 
            case when a.names = '$request->names' then true else false end as comp_names,
            case when a.lastname = '$request->lastname' then true else false end as comp_lastname,
            case when a.id_sex = $request->id_sex then true else false end as comp_sex,
            case when a.f_nac = '$request->f_nac' then true else false end as comp_f_nac,
            case when a.email = '$request->email' then true else false end as comp_email,
            case when a.landline = $request->landline then true else false end as comp_landline,
            case when a.mobile_phone = $request->mobile_phone then true else false end as comp_mobile
            FROM consultants.modify_users as a 
            WHERE a.id_user = $id_user AND a.rut = '$request->rut'
            ");

            if ($compare) {
                $approve = ModifyUser::query()->where(['id_user' => $id_user, 'rut' => $request->rut])->update(['approve' => true]);
            }

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
        } catch (Exception $e) {
            return $e->getMessage();    
        } 
        
        return response()->json([
            'compare' => $compare,
            'message' => 'Record updated successfull'
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
