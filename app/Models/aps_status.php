<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aps_status extends Model
{
    use HasFactory;

    protected $table = 'aps_status';

    protected $fillable = [
        'description'
    ];
}
