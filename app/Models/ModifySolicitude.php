<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifySolicitude extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_solicitude';
    public $incrementing = false;
    protected $table = 'modify_solicitudes';

    protected $fillable = [
        'rut_user',
        'id_prevention_type_detail',
        'assurance',
        'id_location',
        'way_number',
        'id_property_type',
        'property_number',
        'terms_condition',
        'request_date',
        'id_status',
        'rut_employee',
        'visit_date',
        'applicant_at_home',
        'prevention_is_valid',
        'location_is_valid',
        'comments',
        'is_active',
        'rut_match',
        'carer',
        'id_motive',
        'id_dedication',
        'id_remuneration',
        'admission date',
        'dir_wots',
        'latitude',
        'longitude',
        'approve',
        'id_request',
        'approved_by',
        'rejected_by',
        'verificated_by',
        'prevention_type',
        'created_by'
    ];
}
