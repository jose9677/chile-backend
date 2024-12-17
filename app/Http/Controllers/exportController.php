<?php

namespace App\Http\Controllers;

use App\Exports\integramedicaExport;
use App\Exports\reportExport;
use Illuminate\Http\Request;
use App\Exports\usersExport;
use App\Jobs\ExcelReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class exportController extends Controller
{
    public function export()
    {
        return Excel::download(new usersExport, 'users.xlsx');
    }

    public function exportReport()
    {
        $date = date('d-m-Y');
        $fileName = 'REPORTE-Usuarios-Solicitudes Salud a tu Alcance - '.$date.'_'.Str::uuid().'.xlsx';

        $report = Excel::store(new reportExport, config('env.LinkReports').'/'.$fileName);
    }

    public function downloadReport($file_name)
    {
        if (Storage::exists(config('env.reports').$file_name)) {
            return Storage::download(config('env.reports').$file_name);
        }
        else {
            return response()->json(['error' => 'File is not exists'], 404);
        }
    }

    public function exportIntegramedica()
    {
        $date = date('d-m-Y');
        $file_name = 'REPORTE-INTEGRAMEDICA - '.$date.'_'.Str::uuid().'.xlsx';
        
        $report = Excel::store(new integramedicaExport, config('env.LinkIntegramedica').'/'.$file_name);

        //return Excel::download(new integramedicaExport, 'REPORTE-INTEGRAMEDICA - '.$date.'.xlsx');
    }

    public function downloadIntegramedica($file_name)
    {
        if (Storage::exists(config('env.integramedica').$file_name)) {
            return Storage::download(config('env.integramedica').$file_name);
        }
        else {
            return response()->json(['error' => 'File is not exists'], 404);
        }
    }
}
