<?php

namespace Modules\DSE\Database\Seeders;

use Illuminate\Database\Seeder;

class DSEDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(
            [
                DSESettingsSeeder::class,
            ]
        );
    }
}
