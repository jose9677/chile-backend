<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class certificates extends Model
{
    use HasFactory;

    protected $table = 'certificates';
    protected $primaryKey = 'id_certificate';
    public $incrementing = true;

    protected $fillable = [
        'id_certificate',
        'id_simulation',
        'id_status',
        'created_at',
        'updated_at'
    ];
    
}
