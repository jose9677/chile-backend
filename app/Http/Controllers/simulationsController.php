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

    public function addSimulation(Request $request, $rut)
    {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'percent_prevition' => 'required',
            'percent_sec_comp' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $id_request = DB::table('solicitudes')->where('rut_user', $rut)->value('id');
        
        $data = new simulations();
        
        $data->id_request = $id_request;
        $data->id_status = 8;
        $data->percent_prevition = $request->percent_prevition;
        $data->percent_sec_comp = $request->percent_sec_comp;
        
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Bad Request'], 400);
        }
        
        $id_simulation = DB::table('simulations')->where('id_request', $id_request)->value('id');

        return response()->json([
            'message' => 'Simulation created successfull',
            'id_simulation' => $id_simulation
        ], 200);
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
            'percent_prevition' => 'required',
            'percent_sec_comp' => 'required',
            'quantity' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        //$id_prevention_type_detail = DB::table('solicitudes')->where('rut_user', $rut)->value('id_prevention_type_detail');
        $id_service = DB::table('services')->where('cod', $cod)->value('id');
        
        $aps = DB::table('users')->where('rut', $rut)->value('aps');
        $id_request = DB::table('solicitudes')->where('rut_user', $rut)->value('id');
        $assurance = DB::table('solicitudes')->where('rut_user', $rut)->value('assurance');

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

        //$data->calc_cobert_prevention = $request->calc_cobert_prevention;
        //$data->expiration_date = $request->expiration_date;
        $data->id_request = $id_request;
        $data->id_status = 8;
        $data->percent_prevition = $request->percent_prevition;
        $data->percent_sec_comp = $request->percent_sec_comp;

        switch ($prevention_type) {
            case 'FONASA':
                    
                if ($prevention_detail == 'A' and $assurance == true) {
                        $data->calc_secure_comp = ($cost * $request->percent_sec_comp)/100;
                        $sub_total = $cost - $data->calc_secure_comp;
                        $data->sub_total = $sub_total;
                        $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $data->amount_payable = $copago_total;

                        $data->save();
                    }

                if ($prevention_detail == 'A' and $assurance == false) {
                        $data->calc_secure_comp = ($cost * $request->percent_sec_comp);
                        $sub_total = $cost - $data->calc_secure_comp;
                        $data->sub_total = $sub_total;
                        $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $data->amount_payable = $copago_total;

                        $data->save();
                }

                if ($prevention_detail != 'A' and $assurance == true and $aps == false) {
                        $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                        $sub_total = $contribution - $data->calc_secure_comp;
                        $data->sub_total = $sub_total;
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
                            $data->sub_total = $sub_total;
                            $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $data->amount_payable = $copago_total;

                            $data->save();
                        }
                        elseif ($age >= 65) {
                            $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                            $sub_total = $contribution - $data->calc_secure_comp;
                            $data->sub_total = $sub_total;
                            $fonasa_desc = ($sub_total * $request->percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $data->amount_payable = $copago_total;

                            $data->save();
                        }
                }
                break;

                case $prevention_type != 'FONASA':
                        if ($assurance == true and $aps == false) {
                            $data->calc_cobert_prevention = ($cost * $request->percent_prevition)/100;
                            $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                            $sub_total_prevention = ($cost - $data->calc_cobert_prevention);
                            $sub_total_secure_comp = ($sub_total_prevention * 30)/100;
                            $sub_total = ($cost - $sub_total_secure_comp);
                            $data->sub_total = $sub_total;
                            $desc = ($sub_total * 20 )/100;
                            $copago_total = $sub_total - $desc; 
                            $data->amount_payable = $copago_total;
                            $data->save();
                        }
                        
                    break;
            
            default:
                    $data->calc_secure_comp = ($contribution * $request->percent_sec_comp)/100;
                    $sub_total = $contribution - $data->calc_secure_comp;
                    $desc = ($sub_total * $request->percent_prevition)/100;
                    $desc_prevition = ($cost * 20)/100;
                    $copago_total = $cost - $desc_prevition;
                    $data->amount_payable = $copago_total;

                    $data->save();
                break;
        }

            $id_simulation = DB::table('simulations')
            ->join('solicitudes', 'solicitudes.id', '=', 'simulations.id_request')
            ->where('solicitudes.rut_user', $rut)
            ->value('simulations.id');

            $sim_serv = new simulations_services();

            $sim_serv->id_simulation = $id_simulation;
            $sim_serv->id_service = $id_service;
            $sim_serv->quantity = $request->quantity;
            
            $sim_serv->save();

        return response()->json([
            'message' => 'simulation loaded successfull',
            'SubTotal' => $sub_total,
            'Cobert_Prevition' => $data->calc_cobert_prevention,
            'Secure_Comp' => $data->calc_secure_comp,
            'Copago' => $copago_total
        ], 200);
    }

    public function simulation_services($rut, $cod, $quantity)
    {
            $id_simulation = DB::table('simulations')
                    ->join('solicitudes', 'solicitudes.id', '=', 'simulations.id_request')
                    ->where('solicitudes.rut_user', $rut)
                    ->max('simulations.id');
        
            $id_service = DB::table('services')->where('cod', $cod)->value('id');
    
            /*$quantity = DB::table('simulations')
                    ->join('solicitudes', 'solicitudes.id', '=', 'simulations.id_request')
                    ->where('solicitudes.rut_user', $rut)
                    ->get()
                    ->count();*/

            $data = new simulations_services();

            $data->id_simulation = $id_simulation;
            $data->id_service = $id_service;
            $data->quantity = $quantity;
            
            $data->save();

            return response()->json($data, 200);
    }
}
