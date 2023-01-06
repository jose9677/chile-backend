<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\categories;

class categoriesController extends Controller
{
    public function detailsCategories()
    {
        $data = categories::all();
        return response()->json($data, 200);
    }

    public function searchCategory($id)
    {
        $data = categories::findOrFail($id);
        $dataFound = $data;

        return response()->json($dataFound, 200);
    }
}
