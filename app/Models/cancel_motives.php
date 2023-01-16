<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cancel_motives extends Model
{
    use HasFactory;

    protected $table = 'cancel_motives';
    protected $primaryKey = 'id_motive';
    public $incrementing = true;


    protected $fillable = [
        'description'
    ];
}
