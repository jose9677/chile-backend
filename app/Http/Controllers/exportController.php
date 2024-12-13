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

class exportController extends Controller
{
    public function export()
    {
        return Excel::download(new usersExport, 'users.xlsx');
    }

    public function exportReport()
    {
        $date = date('d-m-Y');
        //$todayDate = Carbon::now()->format('H:i:m');
        $fileName = 'REPORTE DE PRUEBA - 3 - '.$date.'.xlsx';
    
        //return Excel::download(new reportExport, 'REPORTE-Usuarios-Solicitudes Salud a tu Alcance - '.$date.'.xlsx');
        //$report = Excel::download(new reportExport, $fileName);

        $link = "reports";
        $report = Excel::store(new reportExport, $link.'/'.$fileName);

        /* $file = $report->getFile();
        dd($file); */
        
        //Storage::disk('posts')->put($fileName, $file);
        //$test = Storage::disk('posts')->files($fileName);
    }

    public function exportIntegramedica()
    {
        $date = date('d-m-Y');

        return Excel::download(new integramedicaExport, 'REPORTE-INTEGRAMEDICA - '.$date.'.xlsx');
    }
}
