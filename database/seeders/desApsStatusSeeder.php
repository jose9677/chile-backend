<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class desApsStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('des_aps_status')->insert([
            'description' => 'REALIZADO CON EXITO'
        ]);

        DB::table('des_aps_status')->insert([
            'description' => 'EN PROCESO'
        ]);

        DB::table('des_aps_status')->insert([
            'description' => 'PENDIENTE'
        ]);

        DB::table('des_aps_status')->insert([
            'description' => 'FINALIZADO CON ERROR'
        ]);
    }
}
