<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\locations;
use App\Models\status;
use Illuminate\Http\Request;

//THIS CONTROLLER GET ALL INFORMATION ABOUT THE DESCRIPTIONS TABLES
class decriptionsController extends Controller
{
    public function info()
    {
        $status = status::all();
        $locations = locations::all();

        return response()->json([
            'status' => $status,
            'locations' => $locations
        ]);
    }
}
