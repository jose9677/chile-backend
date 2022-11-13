<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_request extends Model
{
    use HasFactory;

    protected $fillable = [
        'rut',
        'id_request'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];
}
