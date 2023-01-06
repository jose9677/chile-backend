<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class providers extends Model
{
    use HasFactory;

    protected $table = 'providers';
    protected $primaryKey = 'id_provider';
    public $incrementing = true;

    protected $fillable = [
        'description'
    ];
}
