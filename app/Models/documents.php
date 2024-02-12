<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class documents extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_request',
        'id_document_type',
        'document_path',
        'document_ext',
        'active',
        'id_mod_status'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];
}
