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
        return Excel::download(new reportExport, 'report.xlsx');
    }

    public function exportIntegramedica()
    {
        return Excel::download(new integramedicaExport, 'integramedica.xlsx');
    }
}
