<?php

namespace Database\Seeders;

use App\Models\Accounting\AccountSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountSettingsSeeder extends Seeder
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
            $this->account_settings();
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
        DB::table('account_settings')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function account_settings(): void
    {
        $account_settings = [
            ['id' => '47132fdb-586a-4585-bcce-2509d7550584', 'cash_account' => null, 'customer_liability_account' => 'dd33ef8d-3643-4c6b-965c-078bd5552373', 'customer_cash_account' => 'c83f5f32-ab57-462d-9d0c-86ad3998a1ba', 'order_liability_account' => '785e14c7-2bb9-4588-acf3-37f44d053542', 'order_cash_account' => 'c83f5f32-ab57-462d-9d0c-86ad3998a1ba', 'order_revenue_account' => '715425a8-24a6-4827-a03d-e4775d85b017', 'cmsa_fee_account' => '72da0197-4764-48f4-818f-e39cebcc09d7', 'fidelity_fee_account' => '694771f9-ea20-49ff-8183-b0be2ae932ed', 'dse_fee_account' => '600c9c06-6d3f-4cc5-9f56-28b3dba8e912', 'cds_fee_account' => 'a33fb4b6-75b4-4240-bc74-82354adcea0c', 'vat_account' => '0dd7016e-c7b5-4a7a-91b1-4fa11296cb26', 'receipt_expense_account' => null, 'receipt_cash_account' => null, 'bill_liability_account' => null, 'bill_cash_account' => null, 'bill_expense_account' => null, 'cmsa_payee_account' => '2587f26b-5ed2-4085-b7a5-e9bc0eaecb84', 'fidelity_payee_account' => '332cf872-dc01-42fe-b84b-2c65ecba05fa', 'dse_payee_account' => '6db73d68-c3c4-4450-bcf1-519dd475d0c1', 'cds_payee_account' => '6db73d68-c3c4-4450-bcf1-519dd475d0cc', 'vat_payee_account' => '63242358-7b0d-41cd-9864-2ac1b1dac7a7', 'deleted_at' => null, 'created_at' => '2023-05-30 05:09:07', 'updated_at' => '2023-09-08 00:31:53', 'custodian_account' => '8e99f403-ecc4-41e0-a895-75f1b6e0d0ba', 'custodian_payee' => '63242358-7b0d-41cd-9864-2ac1b1dac7a7', 'other_charges' => '', 'bond_account' => ''],
        ];
        foreach ($account_settings as $account_setting) {
            AccountSetting::create($account_setting);
        }
    }
}
