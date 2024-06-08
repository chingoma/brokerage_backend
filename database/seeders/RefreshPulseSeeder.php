<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshPulseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return false
     */
    public function run()
    {
        $this->truncateLaratrustTables();

    }

    /**
     * Truncates  table
     *
     * @return void
     */
    public function truncateLaratrustTables()
    {
        Schema::disableForeignKeyConstraints();

                DB::table('pulse_entries')->truncate();
                DB::table('pulse_values')->truncate();
                DB::table('pulse_aggregates')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
