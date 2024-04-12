<?php

namespace App\Http\Controllers;

use App\Models\ModifyAditionalData;
use App\Providers\BearerTokenServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModifyAditionalDataController extends Controller
{
    public function getAllAdiData()
    {
        $data = ModifyAditionalData::all();

        return response()->json($data, 200);
    }

    public function loadAditionalData(Request $request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $mod_status = DB::table('users')->where('rut', $request->rut)->value('id_mod_status');

        if ($mod_status == 1) {
            return response()->json(['error' => 'This users has a modify earring '], 400);
        }

        $mod_adi = ModifyAditionalData::create([
            'id_request' => 'required',
            'carer' => 'required',
            'comments' => 'required',
            'percent' => 'required',
            'aps' => 'required',
            'id_motive' => 'required',
            'id_dedication' => 'required',
            'id_remuneration' => 'required',
            'origin' => 'required',
            'approve' => null,
            'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken()),
            'id_mod_status' => 1
        ]);

        //$update = DB::table('users')->where('rut', $request->rut)->update(['id_mod_status' => 1]);
        $update = DB::table('solicitudes')->where('id', $request->id_request)->update(['id_mod_status' => 1]);

        return response()->json([
            'adi_data' => $mod_adi
        ]);
    }

    public function approveAdiChanges(Request $request, $id_mod)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyAditionalData::where([
            'id_mod' => $id_mod
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyAditionalData::query()->where('id_mod', $id_mod)
                                ->update([
                                    'approve' => true,
                                    'id_mod_status' => 2,
                                    'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                                ]);

        return response()->json([
            'response' => 'Changes approved'
        ]);                                
    }

    public function rejectAdiChanges(Request $request, $id_mod)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyAditionalData::where([
            'id_mod' => $id_mod
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyAditionalData::query()->where('id_mod', $id_mod)
                                ->update([
                                    'approve' => false,
                                    'id_mod_status' => 2,
                                    'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                                ]);

        return response()->json([
            'response' => 'Changes rejected'
        ]);                                
    }
}
