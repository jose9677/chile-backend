<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class documents_types extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'document_type'
    ];

    protected $hidden = [

    ];

    protected $casts = [

    ];
}
