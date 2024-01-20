<?php

namespace App\Http\Controllers;

use App\Models\ModifyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ModifyDocumentController extends Controller
{
    public function loadModifyFile(Request $request, $id, $id_document_type)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document_path' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $data = new ModifyDocument();
        $id_doc = ModifyDocument::max('id_document') + 1;

        $data->id_request = $id;
        $data->id_document_type = $id_document_type;

        $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();

            //$comPic = str_replace(' ', '_', $completeFilename).'.'.$extension;
            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$id.'-'.$id_doc.'.'.$extension;

            $path = $request->file('document_path')->storeAs('public/modify', $comPic);
            $data->document_path = $comPic;
            $data->document_ext = $extension;
        }
        $data->save();
        return response()->json(['message' => 'Document uploaded successfull'], 200);
    }
}
