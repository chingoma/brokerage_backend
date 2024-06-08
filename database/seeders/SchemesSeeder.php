<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Schemes\Entities\BondScheme;
use Modules\Schemes\Entities\EquityScheme;

class SchemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTables();

        try {
            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();
            $this->equity_schemes();
            $this->bond_schemes();
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }
    }

    /**
     * Truncates all tables and the users table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('equity_schemes')->truncate();
        DB::table('bond_schemes')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function equity_schemes(): void
    {
        $equity_schemes = [
            ['id' => '0a938b61-ea64-4e9b-8eef-49fa2d02c0ee', 'name' => 'Corporates', 'created_at' => '2023-12-13 14:03:30', 'updated_at' => '2023-12-13 14:03:48', 'broker_fee' => '0', 'mode' => 'flat', 'flat_rate' => '0.008', 'step_one' => '0', 'step_two' => '0', 'step_three' => '0', 'dse_fee' => '0.0014', 'csdr_fee' => '0.000708', 'cmsa_fee' => '0.0014', 'fidelity_fee' => '0.0002', 'deleted_at' => '2023-12-13 14:03:48'],
            ['id' => '16615f82-1ca9-4d3f-aeb3-8023d152e961', 'name' => 'Default', 'created_at' => '2023-12-13 13:52:16', 'updated_at' => '2023-12-13 13:53:29', 'broker_fee' => '0', 'mode' => 'default', 'flat_rate' => '0', 'step_one' => '0.017', 'step_two' => '0.015', 'step_three' => '0.008', 'dse_fee' => '0.0014', 'csdr_fee' => '0.000708', 'cmsa_fee' => '0.0014', 'fidelity_fee' => '0.0002', 'deleted_at' => null],
            ['id' => 'dc4e2ec2-478d-420b-80a7-46eaca74bf5a', 'name' => 'Proprietary', 'created_at' => '2023-12-14 05:37:28', 'updated_at' => '2023-12-14 05:37:28', 'broker_fee' => '0', 'mode' => 'flat', 'flat_rate' => '0', 'step_one' => '0', 'step_two' => '0', 'step_three' => '0', 'dse_fee' => '0.0014', 'csdr_fee' => '0.000708', 'cmsa_fee' => '0.0002', 'fidelity_fee' => '0.0002', 'deleted_at' => null],
            ['id' => 'f40e393a-06c8-4cf1-b613-ff0d107b0286', 'name' => 'Corporate', 'created_at' => '2023-12-13 13:55:00', 'updated_at' => '2023-12-13 13:55:00', 'broker_fee' => '0', 'mode' => 'flat', 'flat_rate' => '0.008', 'step_one' => '0', 'step_two' => '0', 'step_three' => '0', 'dse_fee' => '0.0014', 'csdr_fee' => '0.000708', 'cmsa_fee' => '0.0014', 'fidelity_fee' => '0.0002', 'deleted_at' => null],
        ];
        foreach ($equity_schemes as $equity_scheme) {
            EquityScheme::create($equity_scheme);
        }
    }

    private function bond_schemes(): void
    {
        $bond_schemes = [
            ['id' => '018c7d06-adac-73c3-9606-4ce40bfeed17', 'name' => 'default', 'mode' => 'default', 'broker_fee' => '0', 'flat_rate' => '0', 'step_one' => '0.00062', 'step_two' => '0.0003125', 'dse_fee' => '0.00017', 'csdr_fee' => '0.000118', 'cmsa_fee' => '0.0001', 'deleted_at' => null, 'created_at' => '2023-12-18 05:03:58', 'updated_at' => '2023-12-18 05:03:58'],
            ['id' => '018c7d07-283b-7055-a4ad-23cdc220c6f4', 'name' => 'proprietary', 'mode' => 'flat', 'broker_fee' => '0', 'flat_rate' => '0', 'step_one' => '0', 'step_two' => '0', 'dse_fee' => '0.00017', 'csdr_fee' => '0.000118', 'cmsa_fee' => '0.0001', 'deleted_at' => null, 'created_at' => '2023-12-18 05:04:30', 'updated_at' => '2023-12-18 05:04:30'],
        ];
        foreach ($bond_schemes as $bond_scheme) {
            BondScheme::create($bond_scheme);
        }
    }
}
