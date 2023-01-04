<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class motives extends Model
{
    use HasFactory;

    protected $table = 'motives';
    protected $primaryKey = 'id_motive';
    public $incrementing = true;

    protected $fillable = [
        'description'
    ];
}
