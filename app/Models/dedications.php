<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dedications extends Model
{
    use HasFactory;

    protected $table = 'dedications';
    protected $primaryKey = 'id_dedication';
    public $incrementing = true;

    protected $fillable = [
        'description'
    ];
}
