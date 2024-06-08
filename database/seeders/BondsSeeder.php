<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Bonds\Entities\Bond;

class BondsSeeder extends Seeder
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
            $this->bonds();
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
        DB::table('bonds')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function bonds(): void
    {
        $bonds = [
            ['id' => '018c8c8e-9f75-7235-90d4-a87e862d2dc6', 'price' => null, 'number' => 'CRDB-2023/2028T1', 'isin' => null, 'coupon' => '10.25', 'tenure' => '5', 'issue_date' => '2023-10-23', 'maturity_date' => '2028-10-23', 'issued_amount' => '5000000', 'created_at' => '2023-12-21 05:26:46', 'updated_at' => '2023-12-21 05:26:46', 'deleted_at' => null, 'type' => 'corporate', 'security_name' => 'CRDB-2023/2028T1', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '16.98'],
            ['id' => '018ccab2-8815-73fc-9f08-3a8e8f6129e4', 'price' => null, 'number' => '653', 'isin' => '25', 'coupon' => '12.56', 'tenure' => '25', 'issue_date' => '2023-12-27', 'maturity_date' => '2048-12-28', 'issued_amount' => '4000000000', 'created_at' => '2024-01-02 07:02:27', 'updated_at' => '2024-01-02 07:02:27', 'deleted_at' => null, 'type' => 'government', 'security_name' => '653 TBOND', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '25'],
            ['id' => '018ccac0-2db9-72c9-a608-afc803e03b47', 'price' => null, 'number' => 'NMB-2023/26.T1', 'isin' => 'NMB-2023/26.T1', 'coupon' => '9.5', 'tenure' => '3', 'issue_date' => '2023-11-03', 'maturity_date' => '2026-11-03', 'issued_amount' => '20000000', 'created_at' => '2024-01-02 07:17:21', 'updated_at' => '2024-01-02 07:17:21', 'deleted_at' => null, 'type' => 'corporate', 'security_name' => 'NMB JAMII BOND', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '17'],
            ['id' => '018d1ccc-5058-7000-88d7-2acd096de91c', 'price' => null, 'number' => '', 'isin' => '1146', 'coupon' => null, 'tenure' => '1', 'issue_date' => '2024-01-17', 'maturity_date' => '2025-01-16', 'issued_amount' => '50000000', 'created_at' => '2024-01-18 05:39:28', 'updated_at' => '2024-01-18 05:39:28', 'deleted_at' => null, 'type' => '', 'security_name' => '1 Year T.Bill-1146', 'market' => 'primary', 'category' => 'bill', 'yield_time_maturity' => '11.8'],
            ['id' => '07356b4b-fdb9-4a7c-88d3-c45eb25003b1', 'price' => null, 'number' => 'NMB-2022/25.T4-2M', 'isin' => '-', 'coupon' => '8.50', 'tenure' => '3', 'issue_date' => '2022-03-28', 'maturity_date' => '2025-03-28', 'issued_amount' => '2000000', 'created_at' => '2023-09-27 00:28:22', 'updated_at' => '2023-09-27 00:28:22', 'deleted_at' => null, 'type' => 'corporate', 'security_name' => 'NMB JASIRI BOND', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '14.83'],
            ['id' => '359f9470-488a-44ef-a9d0-e0817300d712', 'price' => null, 'number' => '', 'isin' => 'TZ1996104943', 'coupon' => '7.6', 'tenure' => '2', 'issue_date' => '2023-04-25', 'maturity_date' => '2025-04-27', 'issued_amount' => '100000000', 'created_at' => '2023-09-26 00:28:04', 'updated_at' => '2023-09-26 23:46:59', 'deleted_at' => null, 'type' => '', 'security_name' => '2 Years T.Bond (P50)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '9.6'],
            ['id' => '4b85e04f-31e9-49aa-8937-b0924aae2ded', 'price' => null, 'number' => '', 'isin' => 'TZ1996104992', 'coupon' => '10.25', 'tenure' => '10', 'issue_date' => '2023-05-31', 'maturity_date' => '2033-06-01', 'issued_amount' => '100000000', 'created_at' => '2023-09-27 00:04:20', 'updated_at' => '2023-09-27 00:04:20', 'deleted_at' => null, 'type' => '', 'security_name' => '10 Years T.Bond (P54)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '11.49'],
            ['id' => '4ed3ce4d-392f-46dd-bd50-5eab5de9e5ec', 'price' => null, 'number' => '', 'isin' => 'TZ1996105007', 'coupon' => '11.15', 'tenure' => '15', 'issue_date' => '2023-06-07', 'maturity_date' => '2038-06-08', 'issued_amount' => '500000000', 'created_at' => '2023-09-27 00:09:31', 'updated_at' => '2023-09-27 00:09:31', 'deleted_at' => null, 'type' => '', 'security_name' => '15 Years T.Bond (P69)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '11.99'],
            ['id' => '5201ebdb-55ab-4bd6-ac69-bc09e545bb0a', 'price' => null, 'number' => 'NMB-2022/25.T4', 'isin' => '-', 'coupon' => '8.50', 'tenure' => '3', 'issue_date' => '2022-03-28', 'maturity_date' => '2025-03-28', 'issued_amount' => '45000000', 'created_at' => '2023-09-27 00:26:00', 'updated_at' => '2023-09-27 00:26:00', 'deleted_at' => null, 'type' => 'corporate', 'security_name' => 'NMB JASIRI BOND', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '14.83'],
            ['id' => '562d3bbd-5078-4f2b-a11e-9a97fd696c78', 'price' => null, 'number' => '', 'isin' => 'TZ1996105007', 'coupon' => '11.15', 'tenure' => '15', 'issue_date' => '2023-06-07', 'maturity_date' => '2038-06-08', 'issued_amount' => '500000000', 'created_at' => '2023-09-27 00:10:20', 'updated_at' => '2023-09-27 00:10:20', 'deleted_at' => null, 'type' => '', 'security_name' => '15 Years T.Bond (P72)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '12.10'],
            ['id' => '62d7a095-2be6-434c-8388-2506e0e616d8', 'price' => null, 'number' => '', 'isin' => '635/P18', 'coupon' => '8.60', 'tenure' => '5', 'issue_date' => '2023-05-17', 'maturity_date' => '2023-05-18', 'issued_amount' => '400000000', 'created_at' => '2023-09-26 23:53:34', 'updated_at' => '2023-09-26 23:53:34', 'deleted_at' => null, 'type' => '', 'security_name' => '5 Years T.Bond (P18)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '10.49'],
            ['id' => '71622775-21a1-430c-ac0b-36049f8f788d', 'price' => null, 'number' => '', 'isin' => '635/P19', 'coupon' => '8.60', 'tenure' => '5', 'issue_date' => '2023-05-17', 'maturity_date' => '2023-05-18', 'issued_amount' => '400000000', 'created_at' => '2023-09-26 23:54:46', 'updated_at' => '2023-09-26 23:54:46', 'deleted_at' => null, 'type' => '', 'security_name' => '5 Years T.Bond (P19)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '10.60'],
            ['id' => '7297eb7d-355d-46d2-8b69-b280f6c84809', 'price' => null, 'number' => '', 'isin' => 'TZ1996104992', 'coupon' => '10.25', 'tenure' => '10', 'issue_date' => '2023-05-31', 'maturity_date' => '2033-06-01', 'issued_amount' => '100000000', 'created_at' => '2023-09-27 00:05:25', 'updated_at' => '2023-09-27 00:05:25', 'deleted_at' => null, 'type' => '', 'security_name' => '10 Years T.Bond (P55)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '11.62'],
            ['id' => '7b624407-7a0c-415a-b772-de6d708ef0a4', 'price' => null, 'number' => '', 'isin' => 'TZ1996104943', 'coupon' => '7.6', 'tenure' => '2', 'issue_date' => '2023-04-25', 'maturity_date' => '2025-04-27', 'issued_amount' => '100000000', 'created_at' => '2023-09-26 00:26:56', 'updated_at' => '2023-09-27 00:57:28', 'deleted_at' => null, 'type' => '', 'security_name' => '2 Years T.Bond (P49)', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '9.73'],
            ['id' => '81703856-f857-495a-b16d-960db164713c', 'price' => null, 'number' => '444-10.08-T1-A1', 'isin' => 'TZ1996102624', 'coupon' => '10.08', 'tenure' => '7', 'issue_date' => '2017-03-15', 'maturity_date' => '2024-03-16', 'issued_amount' => '742000000', 'created_at' => '2023-09-30 01:09:08', 'updated_at' => '2023-09-30 01:09:08', 'deleted_at' => null, 'type' => 'government', 'security_name' => '444', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '18.4203'],
            ['id' => 'b458fcef-0464-4f89-bd2f-e902a82f46f5', 'price' => null, 'number' => '', 'isin' => 'TZ1996105015', 'coupon' => '12.10', 'tenure' => '20', 'issue_date' => '2023-06-21', 'maturity_date' => '2043-06-22', 'issued_amount' => '1000000000', 'created_at' => '2023-09-27 00:14:08', 'updated_at' => '2023-09-27 00:14:08', 'deleted_at' => null, 'type' => '', 'security_name' => '20 Years T.Bond', 'market' => 'primary', 'category' => 'bond', 'yield_time_maturity' => '13.25'],
            ['id' => 'b5acdbc7-34e4-4349-a4d2-ea33397350f8', 'price' => null, 'number' => '', 'isin' => '1130/C42', 'coupon' => null, 'tenure' => '1', 'issue_date' => '2023-05-03', 'maturity_date' => '2024-05-02', 'issued_amount' => '200000000', 'created_at' => '2023-09-26 23:37:06', 'updated_at' => '2023-09-26 23:37:06', 'deleted_at' => null, 'type' => '', 'security_name' => '1 Year T.Bill', 'market' => 'primary', 'category' => 'bill', 'yield_time_maturity' => '6.7446'],
            ['id' => 'c00bfc95-c01f-4ced-a338-e3243bd9ee65', 'price' => null, 'number' => 'NBC-2022/27.T1', 'isin' => 'TZ1996104810', 'coupon' => '10', 'tenure' => '5', 'issue_date' => '2022-12-12', 'maturity_date' => '2027-12-13', 'issued_amount' => '1500000', 'created_at' => '2023-09-27 00:50:04', 'updated_at' => '2023-09-27 00:50:04', 'deleted_at' => null, 'type' => 'corporate', 'security_name' => 'NBC TWIGA BOND TRANCHE 1', 'market' => 'secondary', 'category' => 'bond', 'yield_time_maturity' => '14.09'],
        ];
        foreach ($bonds as $bond) {
            Bond::create($bond);
        }
    }
}
