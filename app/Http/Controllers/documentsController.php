<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\documents;
use App\Models\solicitudes;
use App\Models\User;
use finfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;
use SplFileInfo;
use App\Helpers\FileInfoHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class documentsController extends Controller
{
    public function index()
    {
        return view('form');
    }

    public function save(Request $request)
    {
        $document = request()->except('_token');
        
        if ($request->hasFile("document_path")) {
            $file = $request->file("document_path");
            $name = "pdf_".time().".".$file->guessExtension();

            $route = public_path("pdf/".$name);
        }

        if ($file->guessExtension() == "pdf") {
            copy($file, $route);
            documents::insert($document);
        
            return response()->json($document);
        }else {
            return response()->json('NO ES UN PDF');
        }
        
    }

    public function file(Request $request, $rut, $id_document_type)
    {
        $data = new documents();
        
        $query = solicitudes::where('rut_user', $rut)->max('id'); //ID ULTIMA SOLICITUD DEL USUARIO....
        $id_doc = documents::max('id') + 1;
        //$rut = DB::table('solicitudes')->where('id', $query)->value('rut_user');
        
        $email = DB::table('users')->where('rut', $rut)->value('email');

        $data->id_request = $query;
        $data->id_document_type = $id_document_type;

        $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$query.'-'.$id_doc.'.'.$extension;
 
            $path = $request->file('document_path')->storeAs('public/posts', $comPic);
            $data->document_path = $comPic;
            $data->document_ext = $extension;
        }
        if ($data->save()) {
            
            /*$otp = DB::table('users')->where('rut', $rut)->value('otp');

            $mc = new MailController;
            $params = [
                'otp' => $otp
            ];

            $ret = $mc->register($email, $params);
            if($ret !== true){
            abort(500, $ret);
        
            }*/

            documents::query()->where('id', $id_doc)->update(['active' => true]);

            return response()->json(['message' => 'Document '.$id_doc.' uploaded successfull'],200);
        }else {
            $restart = new usersController();
            $restart->restartUser($rut);

            return response()->json(['error' => 'Error to upload document'],400);
        }
    }

    public function indexUrl()
    {
        return view('formUrl');
    }

    public function urlFile($url_doc, $rut, $id_document_type)
    {
        $data = new documents();
        
        $query = solicitudes::where('rut_user', $rut)->max('id');
        $id_doc = documents::max('id') + 1;

        $data->id_request = $query;
        $data->id_document_type = $id_document_type;

        $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');

        if (!empty($url_doc)) {
            $url = $url_doc;
            
            //$file_path = 'C:\xampp\htdocs\salud-a-tu-alcance-backend\storage\app\public\posts\\';
            $file_path = 'http://54.189.74.47/storage/profile_pic/posts';
            
            $imagen = file_get_contents($url);

            $finfo = new finfo(FILEINFO_MIME);

            $b = $finfo->buffer($imagen);
            if($b){
                $mime = explode(";", $b)[0];
                $ext = FileInfoHelper::get_image_ext($mime);
                $nombre = '_'. $documents_types.'_'.'SOL'.$query.'-'.$id_doc.'.'.$ext;
                $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$query.'-'.$id_doc.'.'.$ext;
                file_put_contents($file_path. $nombre, $imagen);

                $data->document_path = $comPic;
                $data->document_ext = $ext;
                $data->save();

                documents::query()->where('id', $id_doc)->update(['active' => true]);

                return response()->json(['message' => 'Document '.$id_doc.' uploaded successfull'],200);
                
            } else {
                abort(500);
            }
            die();
            
        }else {
            return response()->json(['error' => 'Error to upload document'],400);
        }
    }
}
