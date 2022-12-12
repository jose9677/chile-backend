<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\locations;
use App\Models\preventions_details;
use App\Models\preventions_types;
use App\Models\property_types;
use App\Models\status;
use Illuminate\Http\Request;

//THIS CONTROLLER GET ALL INFORMATION ABOUT THE DESCRIPTIONS TABLES
class decriptionsController extends Controller
{
    public function dropdownInfo()
    {
        $status = status::all();
        $property_types = property_types::all();
        $preventions_types_details = preventions_details::all();
        $preventions_types = preventions_types::all();
        $locations = locations::all();

        return response()->json([
            'status' => $status,
            'property_types' => $property_types,
            'preventions_types_details' => $preventions_types_details,
            'preventions_types' => $preventions_types,
            'locations' => $locations
        ]);
    }
}
