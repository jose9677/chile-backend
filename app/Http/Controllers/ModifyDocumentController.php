<?php

namespace App\Http\Controllers;

use App\Models\documents;
use App\Models\ModifyDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ModifyDocumentController extends Controller
{
    public function allDocModify()
    {
        $data = ModifyDocument::all();

        return response()->json($data, 200);
    }

    public function searchDocModify($id_request)
    {
        $data = DB::table('modify_documents')->where('id_request', $id_request)
                    ->orderByDesc('id_document')
                    ->limit(1)
                    ->get();
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function loadModifyFile(Request $request, $id, $id_document_type)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document_path' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        /* $data = new ModifyDocument();
        $id_doc = ModifyDocument::max('id_document') + 1; */

        $data = new documents();
        $id_doc = documents::max('id') + 1;

        $data->id_request = $id;
        $data->id_document_type = $id_document_type;

        $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$id.'-'.$id_doc.'.'.$extension;

            $path = $request->file('document_path')->storeAs('public/posts', $comPic);
            $data->document_path = $comPic;
            $data->document_ext = $extension;
            $data->active = true;
        }
        $data->save();
        
        $doc_mod = ModifyDocument::create([
            'id_request' => $id,
            'id_document_type' => $id_document_type,
            'document_path' => $comPic,
            'document_ext' => $extension,
            'active' => true,
            'approve' => true,
            'id_doc_loaded' => $id_doc
        ]);

        return response()->json(['message' => 'Document uploaded successfull'], 200);
    }

    public function approveDocChanges($id_request, $rut)
    {
        $record = ModifyDocument::where([
            'id_document' => $id_request
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_request', $id_request)->update(['approve' => true]);
        
        return response()->json([
            'response' => 'Changes approved',
            'approved_by' => $this->DataModifer($rut)
        ]);
    }

    public function rejectDocChanges($id_request, $rut)
    {
        $record = ModifyDocument::where([
            'id_document' => $id_request
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_reque$id_request', $id_request)->update(['approve' => false]);
        
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

        /* $gate = DB::table('modify_documents')->where('id_request', $id_request)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        } */

        $document_path = DB::table('modify_documents')->where('id_request', $id_request)->where('id_document_type', $id_document_type)->value('document_path');
        $document_ext = DB::table('modify_documents')->where('id_request', $id_request)->where('id_document_type', $id_document_type)->value('document_ext');

        try {
            $insert = DB::insert(
                "INSERT INTO consultants.documents 
                (id_request, id_document_type, document_path, document_ext, active)
                VALUES($id_request, $id_document_type, '$document_path', '$document_ext', true)
                ");
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return response()->json([
            'response' => 'Changes approved'
            //'approved_by' => $this->DataModifer($request->rut_employee)
        ]);
    }

    public function loadManyDoc(Request $request, $id)
    {
        $requests = $request->all();
        $files = [];
        $nameInputFile = 'file';

        foreach ($requests as $key => $value) {
            if (str_contains($key, $nameInputFile)) {
                array_push($files, $value);
            }
        }

        if (!$files) {
            return response()->json(['error' => 'documents not found'], 400);
        }

        $rulesValidationFiles = [
            'id_document_type' => 'required'
        ];

        /* $gate = DB::table('modify_documents')->where('id_request', $id_request)->value('approve');
        if ($gate == false) {
            return response()->json(['error' => 'This record is not approved'], 400);
        } */

        foreach ($files as $key => $value) {
            try {
                $extensionFile = $value->extension();
            } catch (\Exception $e) {
                $nameFile = $value->getClientOriginalName();
                return response()->json(['error' => 'Faile to load file'], 400);
            }

            $rulesValidationFiles = array_merge($rulesValidationFiles, 
            ['file'.($key) => 'mimetypes:image/jpeg,image/png,image/bmp,application/pdf|max:5120']);
        }

        $filesValidator = Validator::make($request->all(), $rulesValidationFiles);
        
        if ($filesValidator->fails()) {
            $errors = $filesValidator->errors();

            return response()->json(['code' => ['error' => $errors]], 400);
        }

        $id_document_type = $request->id_document_type;
        $index = 0;
        
        foreach ($files as $key => $value) {
            $index++;
            $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');
            $id_doc = documents::max('id') + 1;

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$id.'-'.$id_doc;
            $def_path = $comPic.'.'.$extensionFile;

            $document_path = $this->fileUpload($value, $index, $comPic);

            $dataDocument = documents::create([
                'id_request' => $id,
                'id_document_type' => $id_document_type,
                'document_path' => $def_path,
                'document_ext' => $extensionFile,
                'active' => true
            ]);
        }

        return response()->json(['message' => 'Files uploaded successfull']);
    }

    public function fileUpload($file, $index, $comPic)
    {
        $fileName = $comPic.'.'.$file->extension();

        Storage::disk('modify')->put($fileName, $file);
        $file = Storage::disk('modify')->files($fileName);
       // $url = Storage::disk('public/modify')->url('/');
        $pathUrl = $file[0];

        return $pathUrl;
    }

    public function deleteModDoc($id_request, $id_document_type)
    {
        try {
            $desactive = documents::
            query()->where('id_request', $id_request)
            ->where('id_document_type', $id_document_type)
            ->update(['active' => false]);

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return response()->json(['message' => 'Document desactived'], 200);
    }

    public function deleteDocTest($id)
    {
        try {
            $desactive = documents::query()->where('id', $id)->update(['active' => false]);

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return response()->json(['message' => 'Document desactived'], 200);
    }

    public function deleteManyDoc(Request $request)
    {
        Log::info($request);
        $validator = Validator::make($request->all(),[
            'id_doc' => []
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $doc = $request->id_doc;
        
        for ($i=0; $i < count($doc); $i++) { 
            $desactive = documents::query()->where('id', $doc[$i])->update(['active' => false]);
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
