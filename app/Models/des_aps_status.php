<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class des_aps_status extends Model
{
    use HasFactory;

    protected $table = 'des_aps_status';
    protected $primaryKey = 'id_status';
    //public $incrementing = false;

    protected $fillable = [
        'description'
    ];
}
