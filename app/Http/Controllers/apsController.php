<?php

namespace App\Http\Controllers;

use App\Imports\apsImport;
use App\Models\aps;
use App\Models\aps_status;
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
                if ($this->trucateAPS()) {
                    $status = 1;
                    $des_status = DB::table('aps_status')->where('id_status', $status)->value('description');
                }
                
                $excel = new apsImport;
                if (Excel::import($excel, request()->file('document'))) {
                    $status = 2;
                    $des_status = DB::table('aps_status')->where('id_status', $status)->value('description');
                }
                
                $completeFilename = $request->file('document')->getClientOriginalName();
                $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
                $extension = $request->file('document')->getClientOriginalExtension();
                
                $comPic = str_replace(' ', '_', $completeFilename.'-'.$date).'.'.$extension;
                
                if ($path = $request->file('document')->storeAs('public/aps', $comPic)) {
                    $status = 3;
                    $des_status = DB::table('aps_status')->where('id_status', $status)->value('description');
                }
                
                //INSERTAR DATOS EN LA TABLA DE update_aps...
                
                $data = new update_aps();
                $cant_records = $excel->getRowCount();
    
                $name = DB::table('users')->where('rut', $request->rut)->value('names');
                $lastname = DB::table('users')->where('rut', $request->rut)->value('lastname');
                /* $app_url = env('APP_URL');
                $path = $app_url.'/storage/app/public/aps/'.$comPic; */
                $path = $comPic;
                
                $data->cant_records = $cant_records;
                $data->coincidence = $this->apsCoincidence();
                $data->user = $name.' '.$lastname;
                $data->path = $path;
                $data->id_status = 6;
                $data->update_date = $request->update_date;
                
                try {
                    $data->save();
                    $status = $data->id_status;
                    $des_status = DB::table('aps_status')->where('id_status', $data->id_status)->value('description');
                } catch (\Throwable $th) {
                    return response()->json(['error' => 'Fail to insert new record'], 400);
                }
            }                
        }
        //return back();
        return response()->json([
            'message' => 'File uploaded successfull',
            'status' => $des_status
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
