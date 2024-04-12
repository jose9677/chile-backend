<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyAditionalData extends Model
{
    use HasFactory;

    protected $table = 'modify_aditional_data';
    protected $primaryKey = 'id_mod';
    public $incrementing = false;

    protected $fillable = [
        'id_request',
        'carer',
        'comments',
        'percent',
        'aps',
        'id_motive',
        'id_dedication',
        'id_remuneration',
        'origin',
        'approve',
        'approved_by',
        'rejected_by',
        'verificated_by',
        'created_by',
        'id_mod_status'
    ];
}
