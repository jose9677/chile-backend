<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class aps_statusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('aps_status')->insert([
            'description' => 'LIMPIEZA DE LISTADO ACTUAL'
        ]);

        DB::table('aps_status')->insert([
            'description' => 'CARGA DE LISTADO NUEVO EN BASE DE DATOS'
        ]);

        DB::table('aps_status')->insert([
            'description' => 'RESPALDO DE ARCHIVO FUENTE EN EL SERVIDOR'
        ]);

        DB::table('aps_status')->insert([
            'description' => 'ACTUALIZACION DE VECINOS REGISTRADOS CON APS'
        ]);

        DB::table('aps_status')->insert([
            'description' => 'ACTUALIZACION DE PORCENTAJES DE DESCUENTO'
        ]);

        DB::table('aps_status')->insert([
            'description' => 'PROCESO FINALIZADO CON EXITO'
        ]);
    }
}
