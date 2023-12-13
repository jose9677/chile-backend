<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class update_aps extends Model
{
    use HasFactory;

    protected $table = 'update_aps';
    protected $primaryKey = 'id_aps';
    public $incremneting = 'id_aps';

    protected $fillable = [
        'cant_records',
        'coincidence',
        'user',
        'path',
        'id_status',
        'update_date'
    ];

}
