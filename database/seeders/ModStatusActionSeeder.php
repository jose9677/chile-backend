<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModStatusActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mod_status_action')->insert([
            'description' => 'AÑADIR'
        ]);

        DB::table('mod_status_action')->insert([
            'description' => 'ELIMINAR'
        ]);
    }
}
