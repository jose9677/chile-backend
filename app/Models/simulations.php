<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class simulations extends Model
{
    use HasFactory;

    protected $table = 'simulations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_request',
        'id_status',
        'calc_secure_comp',
        'calc_cobert_prevention',
        'expiration_date',
        'amount_payable'
    ];
}
