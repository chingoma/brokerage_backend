<?php

namespace Database\Seeders;

use App\Models\Accounting\AccountCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountCategoriesSeeder extends Seeder
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
            $this->account_categories();
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
        DB::table('account_categories')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function account_categories(): void
    {
        $account_categories = [
            ['id' => '018d51e9-c0c3-7362-a5b6-e7b16093681a', 'name' => 'Investor Receipt', 'type' => 'Receipt', 'color' => null, 'deleted_at' => null, 'created_at' => '2024-01-29 00:11:30', 'updated_at' => '2024-01-29 00:11:30', 'debit_account' => 'c83f5f32-ab57-462d-9d0c-86ad3998a1ba', 'credit_account' => '785e14c7-2bb9-4588-acf3-37f44d053542'],
            ['id' => '018d51ea-0d0c-7389-9a26-fddd2f306ee4', 'name' => 'Investor Payment', 'type' => 'Payment', 'color' => null, 'deleted_at' => null, 'created_at' => '2024-01-29 00:11:49', 'updated_at' => '2024-01-29 00:11:49', 'debit_account' => '785e14c7-2bb9-4588-acf3-37f44d053542', 'credit_account' => 'c83f5f32-ab57-462d-9d0c-86ad3998a1ba'],
        ];

        foreach ($account_categories as $account_category) {
            AccountCategory::create($account_category);
        }
    }
}
