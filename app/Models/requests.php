<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requests extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_prevention_type',
        'corporation',
        'secure',
        'prevention_doc',
        'id_home_doc',
        'id_ubication',
        'number_way',
        'id_type_property',
        'property_number'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];
}
