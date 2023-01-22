<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\simulations;
use App\Models\simulations_services;
use App\Models\solicitudes;
use App\Models\User;
use Illuminate\Support\Carbon;
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
            //'percent_prevition' => 'required',
            //'percent_sec_comp' => 'required',
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
        WHERE b.rut_user = '$rut'
        GROUP BY
        a.id,
        a.created_at,
        a.expiration_date,
        a.id_status,
        c.status,
        f.id_provider,
        f.description,
        a.amount_payable
        order by a.id");

        return response()->json($query, 200);
    }

    public function calcCopago($id_simulation)
    {
        $sum = DB::table('simulations_services')->where('id_simulation', $id_simulation)->sum('sub_total');
        $calc = DB::update("UPDATE consultants.simulations SET sub_total = $sum WHERE id = $id_simulation");

        $sub_total = DB::table('simulations')->where('id', $id_simulation)->value('sub_total');

        //VALUES
        $id_request = DB::table('simulations')->where('id', $id_simulation)->value('id_request');
        $rut = DB::table('solicitudes')->where('id', $id_request)->value('rut_user');
        $assurance = DB::table('solicitudes')->where('id', $id_request)->value('assurance');
        $aps = DB::table('users')->where('rut', $rut)->value('aps');

        $prevention_detail = DB::table('preventions_details')
        ->select('prevention_detail')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->join('simulations', 'simulations.id_request', '=', 'solicitudes.id')
        ->where('simulations.id', $id_simulation)
        ->value('prevention_detail');

        $prevention_type = DB::table('preventions_types')
        ->select('prevention_type')
        ->join('preventions_details', 'preventions_details.id_prevention_type', '=', 'preventions_types.id')
        ->join('solicitudes', 'solicitudes.id_prevention_type_detail', '=', 'preventions_details.id')
        ->join('simulations', 'simulations.id_request', '=', 'solicitudes.id')
        ->where('simulations.id', $id_simulation)
        ->value('prevention_type');

        $percent_sec_comp = DB::table('simulations')->where('id', $id_simulation)->value('percent_sec_comp');
        $percent_prevition = DB::table('simulations')->where('id', $id_simulation)->value('percent_prevition');

        //CALC-COPAGO
        switch ($prevention_type) {
            case 'FONASA':
                    if ($prevention_detail == 'A' AND $assurance == true) {
                        $calc_secure_comp = ($sub_total * 30)/100;
                        $calc_cobert_prevention = ($sub_total * 50)/100;
                        $balance_2 = $sub_total - $calc_secure_comp;
                        $desc_corporation = ($balance_2 * 50)/100;
                        $total_copago = $balance_2 - $desc_corporation;

                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            calc_cobert_prevention = $calc_cobert_prevention,
                            amount_payable = $total_copago,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                    }

                    if ($prevention_detail == 'A' AND $assurance == false) {
                        $calc_secure_comp = ($sub_total * 0)/100;
                        $calc_cobert_prevention = ($sub_total * 50)/100;
                        $balance_2 = $sub_total;
                        $desc_corporation = ($balance_2 * 50)/100;
                        $total_copago = $balance_2 - $desc_corporation;

                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            calc_cobert_prevention = $calc_cobert_prevention,
                            amount_payable = $total_copago,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                    }

                    if ($prevention_detail != 'A' and $assurance == true and $aps == false) {
                        $calc_secure_comp = ($sub_total * 30)/100;
                        $calc_cobert_prevention = ($sub_total * 20)/100;
                        $balance_2 = $sub_total - $calc_secure_comp;
                        $desc_corporation = ($balance_2 * 20)/100;
                        $total_copago = $balance_2 - $desc_corporation;

                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            calc_cobert_prevention = $calc_cobert_prevention,
                            amount_payable = $total_copago,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");
                    }

                    if ($prevention_detail != 'A' and $assurance == true and $aps == true) {
                        $f_nac = DB::table('users')->where('rut', $rut)->value('f_nac');
                        $age = Carbon::parse($f_nac)->age;
            
                            if ($age < 65) {
                            $calc_secure_comp = ($sub_total * 30)/100;
                            $calc_cobert_prevention = ($sub_total * 30)/100;
                            $balance_2 = $sub_total - $calc_secure_comp;
                            $desc_corporation = ($balance_2 * 30)/100;
                            $total_copago = $balance_2 - $desc_corporation;
                                
                            $data = DB::update(
                                    "UPDATE consultants.simulations 
                                    SET 
                                    calc_secure_comp = $calc_secure_comp,
                                    calc_cobert_prevention = $calc_cobert_prevention,
                                    amount_payable = $total_copago,
                                    sub_total = $sub_total
                                    WHERE id = $id_simulation");
                            }
                            elseif ($age >= 65) {
                            $calc_secure_comp = ($sub_total * 30)/100;
                            $calc_cobert_prevention = ($sub_total * 40)/100;
                            $balance_2 = $sub_total - $calc_secure_comp;
                            $desc_corporation = ($balance_2 * 40)/100;
                            $total_copago = $balance_2 - $desc_corporation;
                                
                            $data = DB::update(
                                    "UPDATE consultants.simulations 
                                    SET 
                                    calc_secure_comp = $calc_secure_comp,
                                    calc_cobert_prevention = $calc_cobert_prevention,
                                    amount_payable = $total_copago,
                                    sub_total = $sub_total
                                    WHERE id = $id_simulation");
                            }
                    }
                break;

                case 'ISAPRE':
                        if ($assurance == true AND $aps == false) {
                            $calc_cobert_prevention = ($sub_total * $percent_prevition)/100;
                            $balance_1 = $sub_total - $calc_cobert_prevention;
                            $calc_secure_comp = ($balance_1 * $percent_sec_comp)/100;
                            $balance_2 = $balance_1 - $calc_secure_comp;
                            $desc_corporation = ($balance_2 * 20)/100;
                            $total_copago = $balance_2 - $desc_corporation;

                            $data = DB::update(
                                "UPDATE consultants.simulations 
                                SET 
                                calc_secure_comp = $calc_secure_comp,
                                calc_cobert_prevention = $calc_cobert_prevention,
                                amount_payable = $total_copago,
                                sub_total = $sub_total
                                WHERE id = $id_simulation");
                        }
                    break;

                    case 'OTRO':
                        if ($assurance == true AND $aps == false) {
                            $calc_cobert_prevention = ($sub_total * $percent_prevition)/100;
                            $balance_1 = $sub_total - $calc_cobert_prevention;
                            $calc_secure_comp = ($balance_1 * $percent_sec_comp)/100;
                            $balance_2 = $balance_1 - $calc_secure_comp;
                            $desc_corporation = ($balance_2 * 20)/100;
                            $total_copago = $balance_2 - $desc_corporation;

                            $data = DB::update(
                                "UPDATE consultants.simulations 
                                SET 
                                calc_secure_comp = $calc_secure_comp,
                                calc_cobert_prevention = $calc_cobert_prevention,
                                amount_payable = $total_copago,
                                sub_total = $sub_total
                                WHERE id = $id_simulation");
                        }
                        break;

                    //AGREGA UN CASE NUEVO PARA EL CASO OTRO...
                    //NO OLVIDES ACTIVAR EL ENVIO DE CORREOS EN LOS METODOS DE REGISTRO...
                    
                    case 'NINGUNO':
                        if ($assurance == false AND $aps == false) {
                        $calc_cobert_prevention = ($sub_total * 0)/100;
                        $calc_secure_comp = ($sub_total * 0)/100;
                        $desc_corporation = ($sub_total * 20)/100;
                        $total_copago = $sub_total - $desc_corporation;
                                
                        $data = DB::update(
                            "UPDATE consultants.simulations 
                            SET 
                            calc_secure_comp = $calc_secure_comp,
                            calc_cobert_prevention = $calc_cobert_prevention,
                            amount_payable = $total_copago,
                            sub_total = $sub_total
                            WHERE id = $id_simulation");         
                        }
            
                        break;
            
            default:
                
                break;
        }

        return response()->json([
            'message' => 'simulation loaded successfull',
            'SubTotal' => $sub_total,
            'Cobert_Prevition' => $calc_cobert_prevention,
            'Secure_Comp' => $calc_secure_comp,
            'Copago' => $total_copago
        ], 200);
    }

    public function generateSimulation($id)
    {
        $data = simulations::findOrFail($id);

        if (!$data) {
            return response()->json(['error' => 'Simulation '.$id.' does not exists'], 400);
        }

        simulations::query()->where(['id' => $id, 'id_status' => 8])->update(['id_status' => 10]);
        return response()->json(['message' => 'Simulation '.$id.' was generated'], 200);
    }

    public function cancelSimulation($id, $id_motive)
    {
        $data = simulations::findOrFail($id);

        if (!$data) {
            return response()->json(['error' => 'Simulation '.$id.' does not exists'], 400);
        }

        simulations::query()->where(['id' => $id, 'id_status' => 8])->update(['id_status' => 9, 'id_motive' => $id_motive]);
        return response()->json(['message' => 'Simulation '.$id.' was canceled'], 200);
    }
}

