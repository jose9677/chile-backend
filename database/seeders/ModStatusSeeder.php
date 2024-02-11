<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mod_status')->insert([
            'description' => 'PENDIENTE'
        ]);

        DB::table('mod_status')->insert([
            'description' => 'APROBADO'
        ]);

        DB::table('mod_status')->insert([
            'description' => 'MODIFICADO'
        ]);

        DB::table('mod_status')->insert([
            'description' => 'RECHAZADO'
        ]);
    }
}
