<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SectorsSeeder extends Seeder
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
            $this->sectors();
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }

    /**
     * Truncates  table
     */
    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('sectors')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function sectors(): void
    {
        $sectors = [
            ['id' => '1', 'name' => 'INDIVIDUAL', 'description' => 'Individuals', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '2', 'name' => 'AGRICULTURE', 'description' => 'Agriculture', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '3', 'name' => 'FISHING', 'description' => 'Fishing', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '4', 'name' => 'FOREST', 'description' => 'Forest', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '5', 'name' => 'HUNTING', 'description' => 'Hunting', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '6', 'name' => 'MINING AND QUARRYING', 'description' => 'Mining And Quarrying', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '7', 'name' => 'WAREHOUSING AND STOR', 'description' => 'Warehousing And Storage', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '8', 'name' => 'TRANSPORT AND COMMUN', 'description' => 'Transport And Communication', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '9', 'name' => 'BUILDING AND CONSTRU', 'description' => 'Building & construction', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '10', 'name' => 'TRADE', 'description' => 'Trade', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '11', 'name' => 'EDUCATION', 'description' => 'Education', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '12', 'name' => 'TOURISM', 'description' => 'Tourism', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '13', 'name' => 'REAL ESTATE', 'description' => 'Real Estate', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '14', 'name' => 'ELECTRICITY', 'description' => 'Electricity', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '15', 'name' => 'LEASING', 'description' => 'Leasing', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '16', 'name' => 'HOTELS AND RESTAURAN', 'description' => 'Hotels And Restaurants', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '17', 'name' => 'FINANCIAL INTERMEDIA', 'description' => 'Financial Intermediaries', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '18', 'name' => 'WATER', 'description' => 'WATER', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '19', 'name' => 'OTHER SERVICES', 'description' => 'Other Services', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '20', 'name' => 'FUEL AND GAS', 'description' => 'Fuel and Gas', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '21', 'name' => 'MANUFACTURING', 'description' => 'Manufacturing', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '22', 'name' => 'HEALTH', 'description' => 'Health', 'deleted_at' => null, 'created_at' => null, 'updated_at' => null],
            ['id' => '83', 'name' => 'CONSULTANCY', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-08-21 15:36:03', 'updated_at' => '2023-08-21 15:36:03'],
            ['id' => '0', 'name' => 'FINANCIAL SERVICES', 'description' => '', 'deleted_at' => '2023-08-21 15:56:06', 'created_at' => '2023-08-21 15:55:54', 'updated_at' => '2023-08-21 15:56:06'],
            ['id' => '41319461', 'name' => 'FINANCIAL SERVICES', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-08-21 15:56:01', 'updated_at' => '2023-08-21 15:56:01'],
            ['id' => '294', 'name' => 'INVESTMENT', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-08-21 15:56:18', 'updated_at' => '2023-08-21 15:56:18'],
            ['id' => '9139', 'name' => 'PHARMACEUTICAL', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-08-21 16:05:31', 'updated_at' => '2023-08-21 16:05:31'],
            ['id' => '66', 'name' => 'CONSTRUCTION', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-09-08 11:36:54', 'updated_at' => '2023-09-08 11:36:54'],
            ['id' => '686beffe-cf19-4d59-b7f8-4ca7b818dcfe', 'name' => 'LOGISTICS', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-09-19 14:36:56', 'updated_at' => '2023-09-19 14:36:56'],
            ['id' => '452a679d-874d-4b26-b48e-6cb0f57c406e', 'name' => 'INDUSTRIAL', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-09-20 12:04:51', 'updated_at' => '2023-09-20 12:04:51'],
            ['id' => 'e28d6641-74f7-490c-9696-e0a6ac81b3e1', 'name' => 'BUSINESS', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-10-19 16:06:27', 'updated_at' => '2023-10-19 16:06:27'],
            ['id' => 'fb7c137f-a100-464c-a138-91bafbdd910d', 'name' => 'INSURANCE', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-10-30 13:59:30', 'updated_at' => '2023-10-30 13:59:30'],
            ['id' => 'd95d5e29-b3bb-4d20-8b9c-5df66f1d0357', 'name' => 'QUARRYING', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-11-07 15:01:38', 'updated_at' => '2023-11-07 15:01:38'],
            ['id' => '8b57e90f-3058-4b4c-8234-a014677b9bdc', 'name' => 'SUNDRIES SHOP', 'description' => '', 'deleted_at' => null, 'created_at' => '2023-12-05 12:31:49', 'updated_at' => '2023-12-05 12:31:49'],
        ];

        foreach ($sectors as $sector) {
            Sector::create($sector);
        }
    }
}
