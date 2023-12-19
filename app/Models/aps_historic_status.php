<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aps_historic_status extends Model
{
    use HasFactory;

    protected $table = 'aps_historic_status';
    protected $primaryKey = ['id_aps', 'id_status'];
    public $incrementing = false;

    protected $fillable = [
        'id_aps',
        'id_status',
        'id_des_status'
    ];
}
