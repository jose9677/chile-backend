<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\cambiarclaveMailable;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\documents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

use App\Mail\ContactanosMailable;
use App\Mail\validarMailable;
use App\Models\providers;
use App\Models\solicitudes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class usersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getNewToken()
    {
        $newToken = Str::random(50);
        return $newToken;
    }

    public function detailsUser()
    {
        $user = User::all();
        return response()->json($user);
    }

   public function search($rut)
   {
        /*$data = new User();
        $datafound = $data->findOrFail($rut);

        return response()->json([$datafound], 200);*/

        $query = DB::select(
            "SELECT
             * ,
             'http://54.189.74.47/storage/profile_pic/'||consultants.users.profile_pic as profile_pic_srv_path
             FROM consultants.users WHERE rut = '$rut'");

        return response()->json($query, 200);
   }

   public function employees()
   {
        $query = DB::select(
            "SELECT * FROM consultants.users WHERE employee = true"
        );

        return response()->json(['data' => $query], 200);
   }

   public function update(Request $request, $rut)
   {
    Log::info($request);
    $validator = Validator::make($request->all(), [
        //'rut' => 'required',
        'names'  => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'f_nac' => 'required',
        'landline' => 'required',
        'mobile_phone' => 'required',
        //'profile_pic' => 'required'

    ]);
    
    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }

    $data = User::findOrFail($rut);
    $data = User::query()->where(['rut' => $request->input('rut'),'employee' => false])->first();

    if (!$data) {
        return response()->json(['error' => 'This user is not a Neightbor'], 400);
    }

    $data->rut = $request->rut;
    $data->names = $request->names;
    $data->lastname = $request->lastname;
    $data->f_nac = $request->f_nac;
    $data->email = $request->email;
    $data->landline = $request->landline;
    $data->mobile_phone = $request->mobile_phone;
    $data->profile_pic = $request->profile_pic;

    try {
        $data->save();
    } catch (\Throwable $th) {
        return response()->json(['error' => 'Update Failed'], 400);
    }

        return response()->json([
            'message' => 'User Updated successfull',
            'data_user' => $data->rut
        ], 200);
   }

   public function updateEmployee(Request $request, $rut)
   {
        Log::info($request);
        $validator = Validator::make($request->all(), [
            //'rut' => 'required',
            'names'  => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'f_nac' => 'required',
            'landline' => 'required',
            'mobile_phone' => 'required',
            //'profile_pic' => 'required',
            'id_rol' => 'required'

        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = User::findOrFail($rut);
        $data = User::query()->where(['rut' => $request->input('rut'),'employee' => true])->first();

        if (!$data) {
            return response()->json(['error' => 'This user is not a Employee'], 400);
        }

        $data->rut = $request->rut;
        $data->names = $request->names;
        $data->lastname = $request->lastname;
        $data->f_nac = $request->f_nac;
        $data->email = $request->email;
        $data->landline = $request->landline;
        $data->mobile_phone = $request->mobile_phone;
        //$data->profile_pic = $request->profile_pic;
        $data->id_rol = $request->id_rol;
    
        try {
            $data->save();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Update Failed'], 400);
        }
            return response()->json([
                'message' => 'User Updated successfull',
                'data_user' => $data->rut
            ], 200);
   }

   public function delete($rut)
   {
        $data = User::find($rut);

        try {
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json(['error' => 'User Does not exist in Data Base'], 400);
        }

        return response()->json(['message' => 'User '.$data->rut.' was deleted successfull'], 200);
   }

   public function consultEmail(Request $request)
   {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = User::where([
            'email' => $request['email']
        ])->get();

        if ($data->count()) {
            return response()->json(['error' => 'Email already exist into DB'], 400);
        }

        return response()->json(['message' => 'Email does not exist into DB'], 200);
   }

   public function validateUser(Request $request)
   {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'rut' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = User::query()->where(['rut' => $request->input('rut')])->first();
        
        if (!$data) {
            return response()->json(['error' => 'User Does Not exists in Data Base'], 400);
        }

        $data = User::where([
            'rut' => $request['rut'],
        ])->get();

        if ($data->count()) {
            /*$user = User::query()->where(['rut' => $request->input('rut'),'email_active' => false])->whereNotNull('otp');

            if($user){
                $this->restartUser($request->rut);
                return response()->json(['error' => 'the user '.$request->rut.' was deleted'], 400);
            }*/

            $email_active = DB::table('users')->where('rut', $request->rut)->value('email_active');
            $otp = DB::table('users')->where('rut', $request->rut)->value('otp');

            if ($email_active == false AND !empty($otp)) {
                $this->restartUser($request->rut);
                return response()->json(['error' => 'the user '.$request->rut.' was deleted'], 400);
            
            }
            elseif($email_active == true AND empty($otp)){
            $api_token = $this->getNewToken();
            $data->api_token = $api_token;
            
            User::query()->where('rut', $request->input('rut'))->update(['api_token' => $api_token]);
            
            $email = DB::table('users')->where('rut', $request->input('rut'))->value('email');
            $app_url = env('APP_URL');
            
            try {    
                $mc = new MailController;

                $params = [
                    'api_token' => $data->api_token,
                    'app_url' => $app_url
                ];
        
                $ret = $mc->validate_User($email, $params);
                if($ret !== true){
                    abort(500, $ret);
                }

            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new validarMailable($data->api_token, $app_url);
            //Mail::to($email)->send($send);

            return response()->json(
                [
                    'message' => 'User Validation successfull',
                    'api_token' => $api_token
                ],  200);
            }   
        }
   }

   public function activeUser($rut)
   {
        $data = User::findOrFail($rut);

        if (!$data) {
            return response()->json(['error' => 'User does not exists'], 400);
        }
        User::query()->where('rut', $rut)->update(['active' => true]);
        return response()->json(['message' => 'User '.$rut.' was actived successfull'], 200);
    }

   public function desactiveUser($rut)
   {
    $data = User::findOrFail($rut);

    if (!$data) {
        return response()->json(['error' => 'User does not exists'], 400);
    }
    User::query()->where('rut', $rut)->update(['active' => false]);
    return response()->json(['message' => 'User '.$rut.' was desactived successfull'], 200);
   }

   public function changePassword(Request $request)
   {
        Log::info($request);
        $validator = Validator::make($request->all(), [    
            'password' => ['required',
                Password::min(8)
                ->letters() //al menos una letra
                ->mixedCase() //al menos una letra minuscula y una mayuscula
                ->numbers() //al menos un caracter numerico
            ],
            'api_token' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $data = User::where([
            'api_token' => $request['api_token']
        ])->get();

        if ($data->count()) {
            $data = new User();
            $data->password = Hash::make($request->password);
            //$rut = DB::table('users')->where('api_token', $request->input('api_token'))->value('rut');
            $email = DB::table('users')->where('api_token', $request->input('api_token'))->value('email');

            User::query()->where('api_token', $request->input('api_token'))->update(['api_token' => NULL, 'password' => $data->password]);
            
            try {    
                $mc = new MailController;
        
                $ret = $mc->newPassword($email);
                if($ret !== true){
                    abort(500, $ret);
                }
                
            } catch (\Throwable $th) {
                return response()->json(['Error' => 'Failed to send Email'], 400);
            }

            //$send = new cambiarclaveMailable();
            //Mail::to($email)->send($send);

            return response()->json(['message' => 'Password Changed successfull'], 200);
        }else {
            return response()->json(['error' => 'Invalid Token'], 400);
        }   
   }

   public function add_supervisor(Request $request, $rut)
   {
        Log::info($request);
        $validator = Validator::make($request->all(), [    
            //'rut' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $assistant = User::findOrFail($rut);

        $assistant = User::query()->where(['rut' => $rut, 'employee' => true ,'id_rol' => 2])->first();

        if (!$assistant) {
            return response()->json(['error' => 'This employee is not a Social Assistant'], 400);
        }

        /*$user = User::query()->where(['rut' => $request->input('rut'), 'employee' => true, 'id_rol' => 3])->first();

        if (!$user) {
            return response()->json(['error' => 'This user is not a Supervisor'],400);
        }*/

        $assistant->supervisor_rut = $request->rut;
        $assistant->save();

        return response()->json(['message' => 'This supervisor '.$request->rut.' was assigned to user '.$rut], 200);        
   }

   public function detailsAssistant($rut)
   {
        $query = DB::select(
            "SELECT * FROM users WHERE supervisor_rut = '$rut'");

        return response()->json(['message' => $query], 200);
   }

   //This method make a consult about all data of Assistants and Supervisors
   public function detailsAssistant_Supervisor()
   {
        $query = DB::select("SELECT * FROM consultants.users WHERE employee = true AND (id_rol = 2 OR id_rol = 3) order by id_rol");
        return response()->json($query);
   }

   public function detailsSupersisor()
   {
        $user = User::where('id_rol', 3)->get();
        return response()->json($user);
   }

   //THIS METHOD GET A LIST ABOUT EVERY SOCIAL ASSISTANT BELONGING TO A ESPECIFIC SUPERVISOR
   public function assistantList($rut)
   {
        $data = User::findOrFail($rut);

        $data = User::query()->where(['rut' => $rut, 'id_rol' => 3])->first();
        
        if (!$data) {
            return response()->json(['error' => 'This user is not a Supervisor'], 400);
        }

        $query = DB::select(
            "SELECT 
            a.*
            FROM consultants.users as a 
            WHERE a.supervisor_rut = '$rut'"
        );

        return response()->json($query, 200);
   }

   public function restartUser($rut)
   {
        $id_request = DB::table('solicitudes')->where('rut_user', $rut)->value('id');

        $i = 0; 
        
        while ($i < 4) {
            switch ($i) {
                case '0':
                    $query = DB::delete("DELETE FROM consultants.documents WHERE id_request = $id_request");
                    $query = DB::select("SELECT setval('consultants.documents_id_seq', (SELECT max(id) FROM consultants.documents), true)");
                    break;
                case '1':
                    $query = DB::delete("DELETE FROM consultants.solicitudes_status WHERE id_request = $id_request");
                    break;
                case '2':
                    $query = DB::delete("DELETE FROM consultants.solicitudes WHERE id = $id_request AND rut_user = '$rut'");
                    $query = DB::select("SELECT setval('consultants.solicitudes_id_seq', (SELECT max(id) FROM consultants.solicitudes), true)");
                    break;
                
                case '3':
                    $query = DB::delete("DELETE FROM consultants.users WHERE rut = '$rut'");
                    break;
                default:
                    # code...
                    break;
            }
            ++$i;
        }
        return response()->json(['message' => 'Restart successfull']);
   }

   //THIS METHOD CONSULTS EVERY DATA BELONGING TO A USER WHITH HIS SOLICITUD, ID_PREVENTION_TYPE AND
   //IT DESCRIPTION, AND GET THE PROVIDER BELOONGING TO THIS USER.
   public function providerUser($rut)
   {
    //OLD QUERY    
    /*$query = DB::select(
        "SELECT 
        a.rut,
        a.names,
        a.lastname,
        c.id_prevention_type,
        d.prevention_type,
        b.id_prevention_type_detail,
        c.prevention_detail,
        h.description as provider_description
        FROM consultants.users as a 
        JOIN consultants.solicitudes as b ON a.rut = b.rut_user
        JOIN consultants.preventions_details as c ON b.id_prevention_type_detail = c.id
        JOIN consultants.preventions_types as d ON c.id_prevention_type = d.id
        JOIN consultants.simulations as e ON e.id_request = b.id
        JOIN consultants.simulations_services as f ON f.id_simulation = e.id
        JOIN consultants.services as g ON g.id = f.id_service
        JOIN consultants.providers as h USING (id_provider)
        WHERE a.rut = '$rut'");*/

        $query = DB::select(
            "SELECT 
            a.rut,
            a.names,
            a.lastname,
            b.assurance,
            c.id_prevention_type,
            d.prevention_type,
            b.id_prevention_type_detail,
            c.prevention_detail
            FROM consultants.users as a 
            JOIN consultants.solicitudes as b ON a.rut = b.rut_user
            JOIN consultants.preventions_details as c ON b.id_prevention_type_detail = c.id
            JOIN consultants.preventions_types as d ON c.id_prevention_type = d.id
            WHERE a.rut = '$rut'");

            $provider = providers::all();

            return response()->json([
                'data_user' => $query,
                'providers' => $provider
            ],200);
   }
}
