<?php

namespace Database\Seeders;

use App\Models\Accounting\FinancialYear;
use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class FinancialYearsSeeder extends Seeder
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
            $business = Business::first();
            $years = [
                [
                    'id' => Uuid::uuid7(),
                    'business_id' => $business->id,
                    'name' => '2024',
                    'year_start' => '2024-01-01 00:00:00',
                    'year_end' => '2024-12-31 23:59:59',
                    'status' => 'active',
                ],
            ];
            foreach ($years as $year) {
                $fYear = FinancialYear::create($year);
                if (strtolower($fYear->status) == 'active') {
                    $business->financial_year = $fYear->id;
                    $business->save();
                }
            }
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
        DB::table('financial_years')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
