<?php

namespace App\Http\Controllers;

use App\Exports\integramedicaExport;
use App\Exports\reportExport;
use Illuminate\Http\Request;
use App\Exports\usersExport;
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

        return Excel::download(new reportExport, 'REPORTE-Usuarios-Solicitudes Salud a tu Alcance - '.$date.'.xlsx');
    }

    public function exportIntegramedica()
    {
        $date = date('d-m-Y');

        return Excel::download(new integramedicaExport, 'REPORTE-INTEGRAMEDICA - '.$date.'.xlsx');
    }
}
