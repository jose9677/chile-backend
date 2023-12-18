<?php

namespace App\Http\Controllers;

use App\Imports\apsImport;
use App\Models\aps;
use App\Models\aps_status;
use App\Models\des_aps_status;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\update_aps;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class apsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('aps');
    }

    public function searchUpdateAps($id_aps)
    {
        $data = DB::table('update_aps')
                    ->select('update_aps.*', 'aps_status.description as status_description')
                    ->join('aps_status', 'aps_status.id_status', '=', 'update_aps.id_status')
                    ->where('id_aps', $id_aps)
                    ->get();

        return response()->json($data, 200);
    }

    public function lastUpdateAps()
    {
        $data = DB::table('update_aps')
                    ->select('update_aps.*', 'aps_status.description as status_description')
                    ->join('aps_status', 'aps_status.id_status', '=', 'update_aps.id_status')
                    ->orderByDesc('id_aps')
                    ->limit(1)
                    ->get();

        return response()->json($data, 200);
    }

    public function import(Request $request)
    {   
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document' => 'required',
            'rut' => 'required',
            'update_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $date = date('d-m-Y');

        $employee = DB::table('users')->where('rut', $request->rut)->value('employee');

        if ($employee == false) {
            return response()->json(['error' => 'This user is not a employee'], 400);
        }
        elseif ($employee == true) {
            if ($request->hasFile('document')) {

                try {
                    $this->trucateAPS();
                    DB::table('update_aps')->insert([
                        'cant_records' => null,
                        'coincidence' => null,
                        'user' => null,
                        'path' => null,
                        'id_status' => 1,
                        'update_date' => null
                    ]);

                    $status_1 = DB::table('aps_status')->where('id_status', 1)->value('description');
                    $step_1 = DB::table('des_aps_status')->where('id_status', 1)->value('description');
                } catch (\Throwable $th) {
                    $step_1 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                }

                $excel = new apsImport;
                try {
                    Excel::import($excel, request()->file('document'));
                    $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                    update_aps::query()->where(['id_aps', $id_aps])->update(['id_status' => 2]);
                    
                    $status_2 = DB::table('aps_status')->where('id_status', 2)->value('description');
                    $step_2 = DB::table('des_aps_status')->where('id_status', 1)->value('description');
                } catch (\Throwable $th) {
                    $step_2 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                }
                
                $completeFilename = $request->file('document')->getClientOriginalName();
                $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
                $extension = $request->file('document')->getClientOriginalExtension();
                
                $comPic = str_replace(' ', '_', $completeFilename.'-'.$date).'.'.$extension;
                
                //INSERTAR DATOS EN LA TABLA DE update_aps...
                
                $data = new update_aps();
                $cant_records = $excel->getRowCount();
    
                $name = DB::table('users')->where('rut', $request->rut)->value('names');
                $lastname = DB::table('users')->where('rut', $request->rut)->value('lastname');
                
                //SUBIR ARCHIVO AL SERVIDOR...
                try {
                    $save = $request->file('document')->storeAs('public/aps', $comPic);
                    $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                    update_aps::query()->where(['id_aps', $id_aps])->update(['id_status' => 3]);
                    
                    $status_3 = DB::table('aps_status')->where('id_status', 3)->value('description');
                    $step_3 = DB::table('des_aps_status')->where('id_status', 1)->value('description');
                } catch (\Throwable $th) {
                    $step_3 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                }
                
                $path = $comPic;
                
                $aps = new AuthController();
                
                try {
                    $aps->updateAps();
                    
                    //ACTUALIZACION DE VECINOS CON APS....
                    $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                    update_aps::query()->where(['id_aps', $id_aps])->update(['id_status' => 4]);
                    
                    $status_4 = DB::table('aps_status')->where('id_status', 4)->value('description');
                    $step_4 = DB::table('des_aps_status')->where('id_status', 1)->value('description');

                    //ACTUALIZACION DE PORCENTAJES APS....
                    $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                    update_aps::query()->where(['id_aps', $id_aps])->update(['id_status' => 5]);
                    
                    $status_5 = DB::table('aps_status')->where('id_status', 5)->value('description');
                    $step_5 = DB::table('des_aps_status')->where('id_status', 1)->value('description');
                } catch (\Throwable $th) {
                    $step_4 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                    $step_5 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                }
                
                $data->cant_records = $cant_records;
                $data->coincidence = $this->apsCoincidence();
                $data->user = $name.' '.$lastname;
                $data->path = $path;
                $data->id_status = 6;
                $data->update_date = $request->update_date;
                
                try {
                    $data->save();
                    $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                    update_aps::query()->where(['id_aps', $id_aps])->update(['id_status' => 6]);
                    
                    $status_6 = DB::table('aps_status')->where('id_status', 6)->value('description');
                    $step_6 = DB::table('des_aps_status')->where('id_status', 1)->value('description');
                } catch (\Throwable $th) {
                    $step_6 = DB::table('des_aps_status')->where('id_status', 4)->value('description');
                    return response()->json(['error' => 'Fail to insert new record'], 400);
                }
            }                
        }
        //return back();
        return response()->json([
            'truncate_aps' => $status_1,
            'status_process' => $step_1,
            'new_aps_list' => $status_2,
            'status_process' => $step_2,
            'save_file_sever' => $status_3,
            'status_process' => $step_3,
            'users_aps' => $status_4,
            'status_process' => $step_4,
            'percent_aps' => $status_5,
            'status_process' => $step_5,
            'final_process' => $status_6,
            'status_process' => $step_6
        ], 200);
    }

    public function downloadFile($file)
    {
        return Storage::download('public/aps/'.$file);
    }

    public function trucateAPS()
    {
        aps::truncate();

        return response()->json(['message' => 'List Aps truncated'], 200);
    }

    public function apsCoincidence()
    {
        $query = DB::table('users')
                      ->join('aps', 'users.rut', '=', 'aps.rut')
                      ->join('solicitudes', 'users.rut', '=', 'solicitudes.rut_user')
                      ->where('solicitudes.is_active', true)
                      ->count();

        return $query;
    }

    public function apsHistoric()
    {
        $data = DB::table('update_aps')
                ->select('update_aps.*', 'aps_status.description')
                ->join('aps_status', 'update_aps.id_status', '=', 'aps_status.id_status')
                ->orderByDesc('id_aps')
                ->get();

        return response()->json($data, 200);
    }

    public function detailsStatus()
    {
        $data = aps_status::all();

        return response()->json($data, 200);
    }

    public function searchStatus($id_status)
    {
        $data = aps_status::FindOrFail($id_status);
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }
}
