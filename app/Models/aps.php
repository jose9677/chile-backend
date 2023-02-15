<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aps extends Model
{
    use HasFactory;

    protected $table = 'aps';
    
    protected $fillable = [
        'rut'
    ];
}
