<?php

namespace App\Http\Controllers;

use App\Models\ModifyAditionalData;
use App\Models\solicitudes;
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

    public function searchAdiData($id_mod)
    {
        $data = DB::table('modify_aditional_data as a')
                  ->join('motives as b', 'b.id_motive', '=', 'a.id_motive')
                  ->join('dedications as c', 'c.id_dedication', '=', 'a.id_dedication')
                  ->join('remunerations as d', 'd.id_remuneration', '=', 'a.id_remuneration')
                  ->join('mod_status as e', 'e.id_mod_status', '=', 'a.id_mod_status')
                  ->where('id_mod', $id_mod)
                  ->get([
                    'a.id_request',
                    'a.carer',
                    'a.comments',
                    'a.percent',
                    'a.aps',
                    'a.id_motive',
                    'b.description as motive',
                    'a.id_dedication',
                    'c.description as dedication',
                    'a.id_remuneration',
                    'd.description as remuneration',
                    'a.origin',
                    'a.approve',
                    'a.approved_by',
                    'a.rejected_by',
                    'a.verificated_by',
                    'a.created_by',
                    'a.id_mod_status',
                    'e.description as mod_status'
                  ]);

        return response()->json($data, 200);
    }

    public function loadAditionalData(Request $request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $mod_status = DB::table('solicitudes')->where('id', $request->id_request)->value('id_adi_status');

        $exists = solicitudes::where([
            'id' => $request->id_request
        ])->get();

        if (!$exists->count()) {
            return response()->json(['error' => 'Solicitude does not exists in DB'], 400);
        }

        if ($mod_status == 1) {
            return response()->json(['error' => 'This users has a modify earring '], 400);
        }

        $mod_adi = ModifyAditionalData::create([
            'id_request' => $request->get('id_request'),
            'carer' => $request->get('carer'),
            'comments' => $request->get('comments'),
            'id_motive' => $request->get('id_motive'),
            'id_dedication' => $request->get('id_dedication'),
            'id_remuneration' => $request->get('id_remuneration'),
            'approve' => null,
            'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken()),
            'id_mod_status' => 1
        ]);

        //$update = DB::table('users')->where('rut', $request->rut)->update(['id_mod_status' => 1]);
        $update = DB::table('solicitudes')->where('id', $request->id_request)->update(['id_adi_status' => 1]);

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
                                    'id_mod_status' => 4,
                                    'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                                ]);

        return response()->json([
            'response' => 'Changes rejected'
        ]);                                
    }
}
