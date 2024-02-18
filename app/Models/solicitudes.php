<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class solicitudes extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

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
        'reg_type',
        'id_mod_status',
        'id_status_emp',
        'origin'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];
}
