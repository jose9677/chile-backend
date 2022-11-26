<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class users_requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'rut',
        'id_request',
        'employee_rut'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];

    protected $primaryKey = ['rut', 'id_request', 'employee_rut'];
    public $incrementing = false;
}
