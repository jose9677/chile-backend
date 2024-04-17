<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyDocument extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_document';
    public $incrementing = false;
    protected $table = 'modify_documents';

    protected $fillable = [
        'id_request',
        'id_document_type',
        'document_path',
        'document_ext',
        'active',
        'rut_employee',
        'approve',
        'id_doc_loaded',
        'id_mod_status',
        'approved_by',
        'rejected_by',
        'verificated_by',
        'created_by',
        'id_mod_action'
    ];
}
