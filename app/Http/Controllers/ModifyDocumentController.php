<?php

namespace App\Http\Controllers;

use App\Models\documents;
use App\Models\ModifyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$id.'-'.$id_doc.'.'.$extension;

            $path = $request->file('document_path')->storeAs('public/modify', $comPic);
            $data->document_path = $comPic;
            $data->document_ext = $extension;
            $data->approve = false;
        }
        $data->save();
        return response()->json(['message' => 'Document uploaded successfull'], 200);
    }

    public function approveDocChanges($id_document, $rut)
    {
        $record = ModifyDocument::where([
            'id_document' => $id_document
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_document', $id_document)->update(['approve' => true]);
        
        return response()->json([
            'response' => 'Changes approved',
            'approved_by' => $this->DataModifer($rut)
        ]);
    }

    public function rejectDocChanges($id_document, $rut)
    {
        $record = ModifyDocument::where([
            'id_document' => $id_document
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_document', $id_document)->update(['approve' => false]);
        
        return response()->json([
            'response' => 'Changes approved',
            'rejected_by' => $this->DataModifer($rut)
        ]);
    }

    public function updateDocData(Request $request, $id_request, $id_document_type)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'rut_employee' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $gate = DB::table('modify_documents')->where('id_request', $id_request)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        }

        $document_path = DB::table('modify_documents')->where('id_request', $id_request)->where('id_document_type', $id_document_type)->value('document_path');
        $document_ext = DB::table('modify_documents')->where('id_request', $id_request)->where('id_document_type', $id_document_type)->value('document_ext');

        $insert = DB::insert(
            "INSERT INTO consultants.documents 
            (id_request, id_document_type, document_path, document_ext, active)
            VALUES($id_request, $id_document_type, '$document_path', '$document_ext', true)
            ");

        return response()->json([
            'response' => 'Changes approved',
            'approved_by' => $this->DataModifer($request->rut_employee)
        ]);
    }

    public function deleteModDoc($id_request, $id_document_type)
    {
        try {
            $desactive = documents::
            query()->where('id_request', $id_request)
            ->where('id_document_type', $id_document_type)
            ->update(['active' => false]);

        } catch (\Throwable $th) {
            return response()->json(['error' => 'Document could not be desatived'], 400);
        }

        return response()->json(['message' => 'Document desactived'], 200);
    }

    public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    }
}
