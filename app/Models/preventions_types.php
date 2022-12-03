<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preventions_types extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'prevention_type'
    ];
}
