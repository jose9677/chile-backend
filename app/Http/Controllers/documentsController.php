<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function file(Request $request, $id_request, $id_document_type)
    {
        $data = new documents();
        //$query = DB::insert("INSERT INTO consultants.documents (id_request, id_document_type) VALUES ($id_request,  $id_document_type)");
        $data->id_request = $id_request;
        $data->id_document_type = $id_document_type;

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();
            
            $comPic = str_replace(' ', '_', $fileNameOnly).'-'.rand() .time().'.'.$extension;
            
            $path = $request->file('document_path')->storeAs('public/posts', $comPic);
            $data->document_path = $comPic;
        }
        if ($data->save()) {
            return response()->json(['message' => 'OK'],200);
        }else {
            return response()->json(['error' => 'ERROR'],400);
        }
    }
}
