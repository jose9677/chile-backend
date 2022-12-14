<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class rolesController extends Controller
{
    public function detailsRoles()
    {
        $query = DB::select("SELECT * FROM roles WHERE id < 5");
        return response()->json($query);
    }
}
