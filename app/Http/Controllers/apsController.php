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
                $data = new update_aps();

                $data->cant_records = null;
                $data->coincidence = null;
                $data->user_name = null;
                $data->path = null;
                $data->id_status = null;
                $data->update_date = null;
                $data->save();

                $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');
                DB::update("UPDATE consultants.update_aps SET id_status = 1 WHERE id_aps = $id_aps");
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 1");

                /* $status_1 = DB::table('aps_status')->where('id_status', 1)->value('description');
                $step_1 = DB::table('des_aps_status')->where('id_status', 1)->value('description'); */

                //PROCESO FINALIZADO...
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 1");

                } catch (\Throwable $th) {
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 1");
                }
                $excel = new apsImport;
                
                try {
                Excel::import($excel, request()->file('document'));
                DB::update("UPDATE consultants.update_aps SET id_status = 2 WHERE id_aps = $id_aps");
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 2");

                /* $status_2 = DB::table('aps_status')->where('id_status', 2)->value('description');
                $step_2 = DB::table('des_aps_status')->where('id_status', 1)->value('description'); */

                //PROCESO FINALIZADO...
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 2");
                } catch (\Throwable $th) {
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 2");
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
                
                try {
                //SUBIR ARCHIVO AL SERVIDOR...
                $save = $request->file('document')->storeAs('public/aps', $comPic);
                DB::update("UPDATE consultants.update_aps SET id_status = 3 WHERE id_aps = $id_aps");
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 3");
                    
                /* $status_3 = DB::table('aps_status')->where('id_status', 3)->value('description');
                $step_3 = DB::table('des_aps_status')->where('id_status', 1)->value('description'); */

                //PROCESO FINALIZADO...
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 3");
                } catch (\Throwable $th) {
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 3");
                }

                $path = $comPic;
                
                $aps = new AuthController();
            
                try {
                $aps->updateAps();
                    
                //ACTUALIZACION DE VECINOS CON APS....
                DB::update("UPDATE consultants.update_aps SET id_status = 4 WHERE id_aps = $id_aps");
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 4");
                    
                /* $status_4 = DB::table('aps_status')->where('id_status', 4)->value('description');
                $step_4 = DB::table('des_aps_status')->where('id_status', 1)->value('description'); */

                //PROCESO FINALIZADO...
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 4");

                //ACTUALIZACION DE PORCENTAJES APS....
                DB::update("UPDATE consultants.update_aps SET id_status = 5 WHERE id_aps = $id_aps");
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 5");
                    
                /* $status_5 = DB::table('aps_status')->where('id_status', 5)->value('description');
                $step_5 = DB::table('des_aps_status')->where('id_status', 1)->value('description'); */

                //PROCESO FINALIZADO....
                DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 5");
                } catch (\Throwable $th) {
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 4");
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 5");
                }
                
                $user_fullName = $name.' '.$lastname;

                try {
                    DB::update(
                        "UPDATE consultants.update_aps 
                        SET 
                        cant_records = $cant_records,
                        coincidence = {$this->apsCoincidence()},
                        user_name =  '$user_fullName',
                        path = '$path',
                        id_status = 6, --SE DISPARA EL TRIGGER consultants.load_status_aps_change_fnc()
                        update_date = '$request->update_date'
                        WHERE id_aps = $id_aps
                        ");
                    
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 2 WHERE id_aps = $id_aps AND id_status = 6");
                    /* $status_6 = DB::table('aps_status')->where('id_status', 6)->value('description');
                    $step_6 = DB::table('des_aps_status')->where('id_status', 1)->value('description');     */

                    //PROCESO FINALIZADO....
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 1 WHERE id_aps = $id_aps AND id_status = 6");
                } catch (\Throwable $th) {
                    DB::update("UPDATE consultants.aps_historic_status SET id_des_status = 4 WHERE id_aps = $id_aps AND id_status = 6");
                }
            }
        }
        //return back();
        /* return response()->json(['OK'
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
        ], 200); */

        return response()->json(['message' => 'Process finished successfull'], 200);
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

    public function ProcessAps()
    {
        $id_aps = DB::table('update_aps')->orderByDesc('id_aps')->limit(1)->value('id_aps');

        $query = DB::table('aps_historic_status')
                    ->select('aps_historic_status.id_aps','aps_status.description as process', 'des_aps_status.description as status')
                    ->join('aps_status', 'aps_status.id_status', '=', 'aps_historic_status.id_status')
                    ->join('des_aps_status', 'des_aps_status.id_status', '=', 'aps_historic_status.id_des_status')
                    ->where('aps_historic_status.id_aps', $id_aps)
                    ->orderBy('aps_historic_status.id_status')
                    ->get();

        return response()->json($query, 200);
    }
}
