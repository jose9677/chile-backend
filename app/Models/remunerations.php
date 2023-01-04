<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class remunerations extends Model
{
    use HasFactory;

    protected $table = 'remunerations';
    protected $primaryKey = 'id_remuneration';
    public $incrementing = true;

    protected $fillable = [
        'description'
    ];
}
