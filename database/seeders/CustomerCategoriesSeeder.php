<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\CRM\Entities\CustomerCategory;
use Ramsey\Uuid\Uuid;

class CustomerCategoriesSeeder extends Seeder
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
            $this->customer_categories();
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
        DB::table('customer_categories')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function customer_categories(): void
    {
        $manager = User::first()->id;
        $customer_categories = [
            ['id' => Uuid::uuid7(), 'name' => 'Retail Client', 'description' => 'normal', 'default' => 'yes', 'created_at' => '2023-07-07 08:50:13', 'updated_at' => '2023-12-18 05:06:22', 'deleted_at' => null, 'manager_id' => $manager, 'equity_scheme' => '16615f82-1ca9-4d3f-aeb3-8023d152e961', 'bond_scheme' => '018c7d06-adac-73c3-9606-4ce40bfeed17'],
            ['id' => Uuid::uuid7(), 'name' => 'High Networth', 'description' => 'High Networth', 'default' => 'no', 'created_at' => '2023-07-15 04:07:38', 'updated_at' => '2023-12-18 05:06:30', 'deleted_at' => null, 'manager_id' => $manager, 'equity_scheme' => 'f40e393a-06c8-4cf1-b613-ff0d107b0286', 'bond_scheme' => '018c7d06-adac-73c3-9606-4ce40bfeed17'],
            ['id' => Uuid::uuid7(), 'name' => 'Corporate', 'description' => 'Corporate', 'default' => 'no', 'created_at' => '2023-07-15 04:08:13', 'updated_at' => '2023-12-18 05:06:36', 'deleted_at' => null, 'manager_id' => $manager, 'equity_scheme' => 'f40e393a-06c8-4cf1-b613-ff0d107b0286', 'bond_scheme' => '018c7d06-adac-73c3-9606-4ce40bfeed17'],
            ['id' => Uuid::uuid7(), 'name' => 'Proprietary Trading', 'description' => 'Proprietary Trading', 'default' => 'no', 'created_at' => '2023-08-25 06:18:24', 'updated_at' => '2023-12-18 05:06:42', 'deleted_at' => null, 'manager_id' => $manager, 'equity_scheme' => 'dc4e2ec2-478d-420b-80a7-46eaca74bf5a', 'bond_scheme' => '018c7d07-283b-7055-a4ad-23cdc220c6f4'],
        ];
        foreach ($customer_categories as $customer_category) {
            CustomerCategory::create($customer_category);
        }
    }
}
