<?php

namespace App\Http\Controllers;

use App\Models\ModifyUser;
use App\Models\User;
use App\Providers\BearerTokenServiceProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class ModifyUserController extends Controller
{
    public function allMoifyUser()
    {
        $data = ModifyUser::all();

        return response()->json($data, 200);
    }

    public function searchModifyUser($id_request)
    {
        $rut = DB::table('modify_solicitudes')->where('id_request', $id_request)->value('rut_user');
        $data = DB::table('modify_users')->where('rut', $rut)->orderByDesc('id_user')->limit(1)->get('*');

        return response()->json($data, 200);
    }

    public function loadUserData(Request $request)
    {
        $user = User::where([
            'rut' => $request['rut']
        ])->get();

        if (!$user->count()) {
            return response()->json(['error' => 'User does not exists in DB'], 400);
        }

        $mod_status = DB::table('users')->where('rut', $request->rut)->value('id_mod_status');

        if ($mod_status == 1) {
            return response()->json(['error' => 'This users has a modify earring '], 400);
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
            'approve' => null, 
            'id_mod_status' => 1,
            'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]);

        $update = DB::table('users')->where('rut', $request->rut)->update(['id_mod_status' => 1]);

        return response()->json([
            'user' => $mod_user
        ]);
    }

    public function approveUserChanges(Request $request, $id_user, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyUser::where([
            'id_user' => $id_user
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyUser::query()->where('id_user', $id_user)
                          ->update([
                            'approve' => true, 
                            'id_mod_status' => 2,
                            'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                         ]);

        //$update_user = User::query()->where('rut', $rut)->update(['id_mod_status' => 2]);
        
        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function rejectUserChanges(Request $request, $id_user, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyUser::where([
            'id_user' => $id_user
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyUser::query()->where('id_user', $id_user)
                          ->update([
                            'approve' => false,
                            'id_mod_status' => 4,
                            'rejected_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                        ]);
        //$mod_status = ModifyUser::query()->where('id_user', $id_user)->update(['id_mod_status' => 4]);
        
        $update_user = User::query()->where('rut', $rut)->update(['id_mod_status' => 4]);
        
        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function compUserData(Request $request)
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

        return response()->json($compare, 200);
    }

    public function updateUserData(Request $request, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $id_user = DB::table('modify_users')->where('rut', $rut)->orderByDesc('id_user')->limit(1)->value('id_user');

        $gate = DB::table('modify_users')->where('id_user', $id_user)->value('approve');
/* 
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        } */

        if ($gate == true) {
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
                    id_sex = $request->id_sex,
                    id_mod_status = 3
                    WHERE rut = '$request->rut'");    

            $verificated_by = DB::table('modify_users')->where('id_user', $id_user)->update(['verificated_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())]);
            } catch (Exception $e) {
                return $e->getMessage();    
            } 
        }elseif ($gate == false) {
            $update = User::query()->where('rut', $request->rut)->update(['id_mod_status' => 4]);
        }
        
        return response()->json([
            'message' => 'Record updated successfull'
        ]);
    }

    public function updateUserDataVIP(Request $request, $rut)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $id_user = DB::table('modify_users')->where('rut', $rut)->orderByDesc('id_user')->limit(1)->value('id_user');
        
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
                id_sex = $request->id_sex,
                id_mod_status = 3
                WHERE rut = '$request->rut'");    

            $verificated_by = DB::table('modify_users')->where('id_user', $id_user)->update(['verificated_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())]);
        } catch (Exception $e) {
            return $e->getMessage();    
        } 
        
        return response()->json([
            'message' => 'Record updated successfull'
        ]);
    }

    /* public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    } */
}
