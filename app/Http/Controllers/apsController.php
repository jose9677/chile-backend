<?php

namespace App\Http\Controllers;

use App\Imports\apsImport;
use App\Models\aps;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class apsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('aps');
    }

    public function import(Request $request)
    {   
        Excel::import(new apsImport,request()->file('document'));
        
        return back();
    }
}
