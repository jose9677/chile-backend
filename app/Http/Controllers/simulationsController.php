<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\simulations;
use App\Models\simulations_services;
use App\Models\solicitudes;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class simulationsController extends Controller
{
    public function detailsSimulation()
    {
        $data = simulations::all();
        return response()->json($data);
    }

    public function searchSimulation($id)
    {
        $data = simulations::findOrFail($id);
        $dataFound = $data;

        return response()->json($dataFound);
    }

    //This method get data about the user and his simulation.
    public function Users_Simulations($rut)
    {
        $query = DB::select("SELECT 
        a.id as id_simulation,
        a.created_at as emition_date,
        a.expiration_date,
        a.id_status,
        d.status,
        b.id as id_request,
        b.rut_user,
        c.names,
        c.lastname
        FROM consultants.simulations as a
        JOIN consultants.solicitudes as b ON a.id_request = b.id
        JOIN consultants.users as c ON b.rut_user = c.rut
        JOIN consultants.status as d ON a.id_status = d.id
        WHERE b.rut_user = '$rut'");

        return response()->json($query, 200);
    }

    public function addSimulation(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_request' => 'required',
            'id_status' => 'required',
            'calc_secure_comp' => 'required',
            'calc_cobert_prevention' => 'required',
            'expiration_date' => 'required',
            'amount_payable' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new simulations();
        $data->id_request = $request->id_request;
        $data->id_status = $request->id_status;
        $data->calc_secure_comp = $request->calc_secure_comp;
        $data->calc_cobert_prevention = $request->calc_cobert_prevention;
        $data->expiration_date = $request->expiration_date;
        $data->amount_payable = $request->amount_payable;
        
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        return response()->json(['message' => 'Simulation created successfull'], 200);
    }

    public function updateSimulation(Request $request, $id)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_request' => 'required',
            'id_status' => 'required',
            'calc_secure_comp' => 'required',
            'calc_cobert_prevention' => 'required',
            'expiration_date' => 'required',
            'amount_payable' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new simulations();
        $data = simulations::findOrFail($id);

        $data->id_request = $request->id_request;
        $data->id_status = $request->id_status;
        $data->calc_secure_comp = $request->calc_secure_comp;
        $data->calc_cobert_prevention = $request->calc_cobert_prevention;
        $data->expiration_date = $request->expiration_date;
        $data->amount_payable = $request->amount_payable;

        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Update Failed'], 400);
        }

        return response()->json([
            'message' => 'Simulation Updated successfull',
            'data_simulation' => $data->id
        ], 200);
    }

    public function deleteSimulation($id)
    {
        $data = simulations::findOrFail($id);
        
        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Simulation does not exists'], 400);
        }

        return response()->json(['message' => 'Simulation '.$id.' was deleted successfull'], 200);
    }

    //SINCE THIS POINT WE START TO WORK WIHT COPAGO CALCS...
    public function calcCopago(Request $request, $rut, $cod)
    {   
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'id_status' => 'required',
            'calc_cobert_prevention' => 'required',
            'expiration_date' => 'required',
            'percent_prevition' => 'required',
            'percent_sec_comp' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id_prevention_type_detail = DB::table('solicitudes')->where('rut_user', $rut)->value('id_prevention_type_detail');
        
        $aps = DB::table('users')->where('rut', $rut)->value('aps');
        $id_request = DB::table('solicitudes')->where('rut_user', $rut)->value('id');
        $assurance = DB::table('solicitudes')->where('rut_user', $rut)->value('assurance');
        $id_service = DB::table('services')->where('cod', $cod)->value('id');

        $cost = DB::table('costs')
        ->select('total_value')
        ->join('services', 'services.id', '=', 'costs.id_service')
        ->where('services.cod', $cod)
        ->value('costs.total_value');

        $contribution = DB::table('costs')
        ->select('contribution')
        ->join('services', 'services.id', '=', 'costs.id_service')
        ->where('services.cod', $cod)
        ->value('costs.contribution');

        $prevention_detail = DB::table('preventions_details')
        ->select('prevention_detail')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $rut)
        ->value('prevention_detail');   

        $prevention_type = DB::table('preventions_types')
        ->select('prevention_type')
        ->join('preventions_details', 'preventions_details.id_prevention_type', '=', 'preventions_types.id')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->where('solicitudes.rut_user', $rut)
        ->value('prevention_type');

        $data = new simulations();

        $data->id_request = $id_request;
        $data->id_status = $request->id_status;
        $data->calc_cobert_prevention = $request->calc_cobert_prevention;
        $data->expiration_date = $request->expiration_date;
        $data->percent_prevition = $request->percent_prevition;
        $data->percent_sec_comp = $request->percent_sec_comp;

        switch ($prevention_type) {
            case 'FONASA':
                    
                if ($prevention_detail == 'A' and $assurance == true) {
                        $data->calc_secure_comp = ($cost * $request->percent_sec_comp)/100;
                        $sub_total = $cost - $data->calc_secure_comp;
                        $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $data->amount_payable = $copago_total;

                        $data->save();
                    }

                if ($prevention_detail == 'A' and $assurance == false) {
                        $data->calc_secure_comp = ($cost * $request->percent_sec_comp);
                        $sub_total = $cost;
                        $fonasa_desc = ($sub_total * $request->precent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $data->amount_payable = $copago_total;

                        $data->save();
                }

                if ($prevention_detail != 'A' and $assurance == true and $aps == false) {
                        $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                        $sub_total = $contribution - $data->calc_secure_comp;
                        $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $data->amount_payable = $copago_total;

                        $data->save();
                }

                if ($prevention_detail != 'A' and $assurance == true and $aps == true) {
                    $age = DB::select("SELECT date_part('year',age(f_nac)) FROM consultants.users WHERE rut = '$rut'");
                        if ($age < 65) {
                            $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                            $sub_total = $contribution - $data->calc_secure_comp;
                            $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $data->amount_payable = $copago_total;

                            $data->save();
                        }
                        elseif ($age >= 65) {
                            $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                            $sub_total = $contribution - $data->calc_secure_comp;
                            $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $data->amount_payable = $copago_total;

                            $data->save();
                        }
                }
                break;

                case $prevention_type != 'FONASA':
                        if ($assurance == true) {
                        $desc = ($cost * $request->calc_cobert_prevention)/100; //5184...
                        $sub_total = $cost - $desc;  // 7776...
                        $data->calc_secure_comp = ($sub_total * $request->percent_sec_comp)/100;// 2333
                        $cobert_sec_comp = $data->calc_secure_comp; // 2333
                        $sub_total_comp = $cost - $cobert_sec_comp; // 10627
                        $prevention_desc = ($sub_total_comp * 20)/100; // 2125
                        $copago_total = $sub_total_comp - $prevention_desc;

                        $data->save();
                        }
                        
                    break;
            
            default:
                # code...
                break;
        }

        $id_simulation = simulations::max('id');
        $sim_serv = new simulations_services();
        $sim_serv->id_simulation = $id_simulation;
        $sim_serv->id_service = $id_service;
        $sim_serv->quantity = $request->get('quantity');

        $sim_serv->save();

        return response()->json([
            'message' => 'simulation loaded successfull',
            'SubTotal' => $sub_total,
            'Secure_Comp' => $data->calc_secure_comp,
            'Copago' => $copago_total
        ], 200);
    }

}
