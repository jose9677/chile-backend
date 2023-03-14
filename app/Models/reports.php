<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reports extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'fecha_inscripcion',
        'rut',
        'nombres',
        'apellidos',
        'fecha_de_nac',
        'sexo',
        'edad',
        'email',
        'tel_fijo',
        'tel_móvil',
        'calle',
        'número_calle',
        'casa_dpto_ofic',
        'número_casa_dpto_ofic',
        'aps',
        'nro_solicitud',
        'tipo_previsión',
        'detalle_de_previsión',
        'seguro_complementario',
        'cuidador',
        'motivo',
        'dedicación',
        'remuneración',
        'estado_de_solicitud',
        'descuento_corporacion'
    ];
}
