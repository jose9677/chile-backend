<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricReports extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_doc';
    protected $table = 'historic_reports';
    public $incrementing = false;

    protected $filable = [
        'rut',
        'route',
        'method',
        'file_name',
        'code_response',
        'active'
    ];
}
