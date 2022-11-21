<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\documents;
use Illuminate\Http\Request;

class documentsController extends Controller
{
    public function index()
    {
        return view('form');
    }

    public function save(Request $request)
    {
        $document = request()->except('_token');
        documents::insert($document);
        
        return response()->json($document);

        /*if ($request->hasFile("document_path")) {
            $file = $request->file("document_path");
            $name = "pdf_".time().".".$file->guessExtension();

            $route = public_path("pdf/".$name);
        }

        if ($file->guessExtension() == "pdf") {
            copy($file, $route);
        }else {
            return response()->json('NO ES UN PDF');
        }*/
        
    }
}
