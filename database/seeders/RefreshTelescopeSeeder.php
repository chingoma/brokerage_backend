<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshTelescopeSeeder extends Seeder
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

        DB::table('telescope_entries')->truncate();
        DB::table('telescope_entries_tags')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
