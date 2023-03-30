<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class certificate_status extends Model
{
    use HasFactory;

    protected $table = 'certificate_status';
    protected $primaryKey = 'id_status';
    public $incrementing = true;

    protected $fillable = [
        'id_status',
        'description'
    ];
    
}
