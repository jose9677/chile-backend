<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\usersExport;
use Maatwebsite\Excel\Facades\Excel;

class exportController extends Controller
{
    public function export()
    {
        return Excel::download(new usersExport, 'users.xlsx');
    }
}
