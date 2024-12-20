<?php

namespace App\Models;

use App\Providers\BearerTokenServiceProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function reportsHistoric(Request $request, $fileName)
    {
        $log = new HistoricReports();
        $cod = new Response();

        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Request without bearer token'], 400);
        }

        $rut = BearerTokenServiceProvider::getRutByBearerToken($request->bearerToken());

        //$rut = '9999999-K';

        $log->rut = $rut;
        $log->route = $request->url();
        $log->method = $request->method();
        $log->file_name = $fileName;
        $log->code_response = $cod->getStatusCode();
        $log->active = true;

        $log->save();
    }
}
