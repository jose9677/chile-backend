<?php

namespace App\Http\Controllers;

use App\Models\documents;
use App\Models\ModifyDocument;
use App\Providers\BearerTokenServiceProvider;
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
        $data = DB::table('modify_documents')->where(['id_request' => $id_request, 'active' => true])
                    ->orderByDesc('id_document')
                    ->get();
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }

    public function loadModifyFile(Request $request, $id, $id_document_type)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document_path' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        /* $mod_status = DB::table('documents')->where('id', $id)->value('id_mod_status');

        if ($mod_status == 1) {
            return response()->json(['error' => 'This users has a modify earring '], 400);
        } */

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
            $data->id_mod_status = 1;
        }
        $data->save();

        /* $delete_doc = $request->delete_doc;

        for ($i=0; $i < count($delete_doc); $i++) { 
            $delete = ModifyDocument::query()->where('id_doc', $delete_doc[$i])->update(['id_mod_action' => 2]);
        } */
        
        /* $doc_mod = ModifyDocument::create([
            'id_request' => $id,
            'id_document_type' => $id_document_type,
            'document_path' => $comPic,
            'document_ext' => $extension,
            'active' => true,
            'approve' => true,
            'id_doc_loaded' => $id_doc,
            'id_mod_status' => 1,
            'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]); */

        return response()->json(['message' => 'Document uploaded successfull'], 200);
    }

    public function loadListFiletoModify(Request $request, $id_request, $id_document_type)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        Log::info($request);
        $validator = Validator::make($request->all(),[
            'document_path' => 'required',
            'doc' => []
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
 
        $documents_types = DB::table('documents_types')->where('id', $id_document_type)->value('document_type');

        if ($request->hasFile('document_path')) {
            $completeFilename = $request->file('document_path')->getClientOriginalName();
            $fileNameOnly = pathinfo($completeFilename, PATHINFO_FILENAME);
            $extension = $request->file('document_path')->getClientOriginalExtension();

            $comPic = str_replace(' ', '_', $documents_types).'_'.'SOL'.$id_request.'-'.'.'.$extension;

            $path = $request->file('document_path')->storeAs('public/modify', $comPic);
            
            $doc_mod = ModifyDocument::create([
                'id_request' => $id_request,
                'id_document_type' => $id_document_type,
                'document_path' => $comPic,
                'document_ext' => $extension,
                'active' => true,
                'approve' => null,
                //'id_doc_loaded' => $id_doc,
                'id_mod_status' => 1,
                'created_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken()),
                'id_mod_action' => 1
            ]);
        }

        $doc = json_decode($request->doc);

        for ($i=0; $i < count($doc); $i++) { 
            $delete_doc = DB::insert(
                "INSERT INTO consultants.modify_documents(
                    id_request, id_document_type, document_path,
                    document_ext, active, id_doc_loaded, id_mod_status,
                    id_mod_action
                )
                SELECT 
                    id_request, id_document_type, document_path,
                    document_ext, active, id, id_mod_status, 2
                FROM consultants.documents WHERE id = $doc[$i] AND id_request = $id_request AND active = true"
            );
        }

        return response()->json(['message' => 'Document uploaded successfull'], 200);
    }

    public function approveDocChanges(Request $request, $id_request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyDocument::where([
            'id_request' => $id_request,
            'active' => true
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_request', $id_request)
                              ->update([
                                'approve' => true, 
                                'id_mod_status' => 2,
                                'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                            ]);
        
        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function rejectDocChanges(Request $request, $id_request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $record = ModifyDocument::where([
            'id_request' => $id_request,
            'active' => true
        ])->get();

        if (!$record->count()) {
            return response()->json(['error' => 'This record is not exists'], 400);
        }

        $data = ModifyDocument::query()->where('id_request', $id_request)
                              ->update([
                                'approve' => false,
                                'id_mod_status' => 4,
                                'rejected_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
                            ]);
        
        return response()->json([
            'response' => 'Changes approved'
        ]);
    }

    public function updateDocData(Request $request)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        Log::info($request);
        $validator = Validator::make($request->all(),[
            'doc_add' => [],
            'doc_del' => []
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $doc_add = $request->doc_add;
        $doc_del = $request->doc_del;

        for ($i=0; $i < count($doc_add); $i++) { 
            $gate = DB::table('modify_documents')->where('id_document', $doc_add[$i])->value('approve');

            if ($gate == false) {
                return response()->json(['error' => 'This record is not approved'], 400);
            } 

            $record = DB::table('modify_documents')->where('id_document', $doc_add[$i])->first();

            if ($record->id_mod_action == 1) {
                try {
                    $insert = DB::table('documents')->insert([
                        'id_request' => $record->id_request,
                        'id_document_type' => $record->id_document_type,
                        'document_path' => $record->document_path,
                        'document_ext' => $record->document_path,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'active' => true,
                        'id_mod_status' => 3
                    ]);

                    $desactive_mod = ModifyDocument::query()->where('id_doc_loaded', $doc_add[$i])->update(['active' => false]);
        
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        }

        for ($i=0; $i < count($doc_del); $i++) { 
            try {
                $desactive = documents::query()->where('id', $doc_del[$i])->update(['active' => false, 'id_mod_status' => 3]);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return response()->json([
            'response' => 'Changes approved',
            'approved_by' => BearerTokenServiceProvider::getUserByBearerToken($request->bearerToken())
        ]);
    }

    public function loadManyDoc(Request $request, $id)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

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

    /* public function DataModifer($rut)
    {
        $names = DB::table('users')->where('rut', $rut)->value('names');
        $lastname = DB::table('users')->where('rut', $rut)->value('lastname');

        $response = $names.' '.$lastname;

        return Str::upper($response);
    } */
}
