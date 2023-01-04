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

    public function detailsActiveRoles()
    {
        $query = DB::select("SELECT * FROM roles WHERE active = true");
        return response()->json($query, 200);
    }

    public function activeRol($id)
    {
        $data = roles::findOrFail($id);

        if (!$data) {
            return response()->json(['error' => 'Rol is not exists'], 400);
        }

        roles::query()->where('id', $id)->update(['active' => true]);

        return response()->json(['message' => 'Rol '.$id.' has been desactived'], 200);
    }

    public function desactiveRol($id)
    {
        $data = roles::findOrFail($id);

        if (!$data) {
            return response()->json(['error' => 'Rol is not exists'], 400);
        }

        roles::query()->where('id', $id)->update(['active' => false]);

        return response()->json(['message' => 'Rol '.$id.' has been actived'], 200);
    }
}
