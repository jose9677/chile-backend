<?php

namespace Database\Seeders;

use App\Models\des_aps_status;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        //$this->call(aps_statusSeeder::class);
        //$this->call(desApsStatusSeeder::class);
        //$this->call(ModStatusSeeder::class);
        $this->call(ModStatusActionSeeder::class);
    }
}
