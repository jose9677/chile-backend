<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModStatus extends Model
{
    use HasFactory;

    protected $table = 'mod_status';
    protected $primaryKey = 'id_mod_status';

    protected $fillable = [
        'description'
    ];
}
