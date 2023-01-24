<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class google_tokens extends Model
{
    use HasFactory;

    protected $table = 'google_tokens';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'apigee_refresh_token',
        'apigee_token',
        'apigee_token_expires',
        'apigee_refresh_token_expires',
        'created_at',
        'updated_at'
    ];
}
