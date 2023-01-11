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
        
        $id_simulation = DB::table('simulations')->where('id_request', $id_request)->max('id');

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
    public function detailsCopago($id)
    {
        $query = DB::select("SELECT 
        a.id as id_simulation,
        a.created_at as emition_date,
        a.expiration_date,
        b.rut_user,
        c.names,
        c.lastname,
        a.id_status,
        d.status,
        a.percent_prevition,
        a.percent_sec_comp,
        g.id_provider,
        g.description as provider_description,
        f.id as id_service,
        f.cod as services_cod,
        h.description as category_description,
        f.description as service_description,
        i.total_value,
        i.contribution,
        e.quantity,
        a.amount_payable
        FROM consultants.simulations as a 
        JOIN consultants.solicitudes as b ON a.id_request = b.id
        JOIN consultants.users as c ON b.rut_user = c.rut
        JOIN consultants.status as d ON a.id_status = d.id
        JOIN consultants.simulations_services as e ON a.id = e.id_simulation
        JOIN consultants.services as f ON e.id_service = f.id
        JOIN consultants.providers as g ON f.id_provider = g.id_provider
        JOIN consultants.categories as h ON f.id_category = h.id
        JOIN consultants.costs as i ON i.id_service = f.id
        WHERE a.id = $id");

        return response()->json($query, 200);
    }

    public function detailsCerticate($rut)
    {
        $query = DB::select("SELECT 
        a.id as id_simulation,
        a.created_at as emition_date,
        a.expiration_date,
        a.id_status,
        c.status,
        f.id_provider,
        f.description as provider_description,
        a.amount_payable as copago_value
        FROM consultants.simulations as a 
        JOIN consultants.solicitudes as b ON a.id_request = b.id
        JOIN consultants.status as c ON a.id_status = c.id
        JOIN consultants.simulations_services as d ON a.id = d.id_simulation
        JOIN consultants.services as e ON e.id = d.id_service
        JOIN consultants.providers as f ON e.id_provider = f.id_provider
        WHERE b.rut_user = '$rut'");

        return response()->json($query, 200);
    }

    public function calcCopago($rut)
    {   
        $aps = DB::table('users')->where('rut', $rut)->value('aps');
        $id_request = DB::table('solicitudes')->where('rut_user', $rut)->value('id');
        $assurance = DB::table('solicitudes')->where('rut_user', $rut)->value('assurance');

        $id_simulation = DB::table('simulations')
            ->join('solicitudes', 'solicitudes.id', '=', 'simulations.id_request')
            ->where('solicitudes.rut_user', $rut)
            ->max('simulations.id');
        
        $id_service = DB::table('simulations_services')
                        ->join('simulations', 'simulations.id', '=', 'simulations_services.id_simulation')
                        ->where('simulations.id', $id_simulation)
                        ->value('simulations_services.id_service');

        $cost = DB::table('costs')
        ->select('total_value')
        ->join('services', 'services.id', '=', 'costs.id_service')
        ->where('services.id', $id_service)
        ->value('costs.total_value');

        $contribution = DB::table('costs')
        ->select('contribution')
        ->join('services', 'services.id', '=', 'costs.id_service')
        ->where('services.cod', $id_service)
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

        $percent_sec_comp = DB::table('simulations')->where('id', $id_simulation)->value('percent_sec_comp');
        $percent_prevition = DB::table('simulations')->where('id', $id_simulation)->value('percent_prevition');

        switch ($prevention_type) {
            case 'FONASA':
                    
                if ($prevention_detail == 'A' and $assurance == true) {
                        $calc_secure_comp = ($cost * $percent_sec_comp)/100;
                        $sub_total = $cost - $calc_secure_comp;
                        //$data->sub_total = $sub_total;
                        $fonasa_desc = ($sub_total * $percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $amount_payable = $copago_total;

                        //$data->save();
                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            amount_payable = $amount_payable,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                    }

                if ($prevention_detail == 'A' and $assurance == false) {
                        $calc_secure_comp = ($cost * $percent_sec_comp);
                        $sub_total = $cost - $calc_secure_comp;
                        //$data->sub_total = $sub_total;
                        $fonasa_desc = ($sub_total * $percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $amount_payable = $copago_total;

                        //$data->save();
                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            amount_payable = $amount_payable,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                }

                if ($prevention_detail != 'A' and $assurance == true and $aps == false) {
                        $calc_secure_comp = ($contribution * $percent_sec_comp)/100;
                        $sub_total = $contribution - $calc_secure_comp;
                        //$data->sub_total = $sub_total;
                        $fonasa_desc = ($sub_total * $percent_prevition)/100;
                        $copago_total = $sub_total - $fonasa_desc;
                        $amount_payable = $copago_total;

                        //$data->save();
                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            amount_payable = $amount_payable,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                }

                if ($prevention_detail != 'A' and $assurance == true and $aps == true) {
                    $age = DB::select("SELECT date_part('year',age(f_nac)) FROM consultants.users WHERE rut = '$rut'");
                        if ($age < 65) {
                            $calc_secure_comp = ($contribution * $percent_sec_comp)/100;
                            $sub_total = $contribution - $calc_secure_comp;
                            //$data->sub_total = $sub_total;
                            $fonasa_desc = ($sub_total * $percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $amount_payable = $copago_total;

                            //$data->save();
                            $data = DB::update(
                                "UPDATE consultants.simulations 
                                SET 
                                calc_secure_comp = $calc_secure_comp,
                                amount_payable = $amount_payable,
                                sub_total = $sub_total
                                WHERE id = $id_simulation");
                        }
                        elseif ($age >= 65) {
                            $calc_secure_comp = ($contribution * $percent_sec_comp)/100;
                            $sub_total = $contribution - $calc_secure_comp;
                            //$data->sub_total = $sub_total;
                            $fonasa_desc = ($sub_total * $percent_prevition)/100;
                            $copago_total = $sub_total - $fonasa_desc;
                            $amount_payable = $copago_total;

                            //$data->save();
                            $data = DB::update(
                                "UPDATE consultants.simulations 
                                SET 
                                calc_secure_comp = $calc_secure_comp,
                                amount_payable = $amount_payable,
                                sub_total = $sub_total
                                WHERE id = $id_simulation");
                        }
                }
                break;

                case $prevention_type != 'FONASA':
                        if ($assurance == true and $aps == false) {
                            $calc_cobert_prevention = ($cost * $percent_prevition)/100;
                            $calc_secure_comp = ($contribution * $percent_sec_comp)/100;
                            $sub_total_prevention = ($cost - $calc_cobert_prevention);
                            $sub_total_secure_comp = ($sub_total_prevention * 30)/100;
                            $sub_total = ($cost - $sub_total_secure_comp);
                            //$data->sub_total = $sub_total;
                            $desc = ($sub_total * 20 )/100;
                            $copago_total = $sub_total - $desc; 
                            $amount_payable = $copago_total;
                            //$data->save();
                            $data = DB::update(
                                "UPDATE consultants.simulations 
                                SET 
                                calc_secure_comp = $calc_secure_comp,
                                calc_cobert_prevention = $calc_cobert_prevention,
                                amount_payable = $amount_payable,
                                sub_total = $sub_total
                                WHERE id = $id_simulation");
                        }
                        
                    break;
            
            default:
                    $calc_secure_comp = ($contribution * $percent_sec_comp)/100;
                    $sub_total = $contribution - $calc_secure_comp;
                    $desc = ($sub_total * $percent_prevition)/100;
                    $desc_prevition = ($cost * 20)/100;
                    $copago_total = $cost - $desc_prevition;
                    $amount_payable = $copago_total;

                    //$data->save();
                    $data = DB::update(
                        "UPDATE consultants.simulations 
                        SET 
                        calc_secure_comp = $calc_secure_comp,
                        amount_payable = $amount_payable,
                        sub_total = $sub_total
                        WHERE id = $id_simulation");
                break;
        }

        return response()->json([
            'message' => 'simulation loaded successfull',
            'SubTotal' => $sub_total,
            'Cobert_Prevition' => $calc_cobert_prevention,
            'Secure_Comp' => $calc_secure_comp,
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
