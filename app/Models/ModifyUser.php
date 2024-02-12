<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyUser extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_user';
    protected $table = 'modify_users';
    public $incrementing = false;

    protected $fillable = [
        'rut',
        'names',
        'lastname',
        'f_nac',
        'email',
        'password',
        'landline',
        'mobile_phone',
        'employee',
        'supervisor_rut',
        'otp',
        'email_active',
        'profile_pic',
        'api_token',
        'id_rol',
        'created_at',
        'active',
        'aps',
        'id_sex',
        'percent',
        'origin',
        'rut_employee',
        'approve',
        'id_mod_status'
    ];
}
