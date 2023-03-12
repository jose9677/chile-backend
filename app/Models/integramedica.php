<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class integramedica extends Model
{
    use HasFactory;

    protected $table = 'integramedica';
    
    protected $fillable = [
        'codigo_entidad',
        'rut',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'relacion',
        'fecha_ingreso',
        'fecha_inicio_vigencia',
        'fecha_término_vigencia',
        'codigo_plan',
        'rut_titular',
        'tipo_evento'
    ];
}
