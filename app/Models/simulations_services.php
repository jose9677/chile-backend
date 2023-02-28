<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class simulations_services extends Model
{
    use HasFactory;

    protected $table = 'simulations_services';
    public $incrementing = false;
    //protected $primaryKey = ['id_simulation', 'id_service', 'quantity'];

    protected $fillable = [
        'id_simulation',
        'id_service',
        'quantity',
        'balance',
        'sub_total',
        'sub_total_aporte'
    ];

    protected $hidden = [

    ];

    protected $cast = [

    ];

}
