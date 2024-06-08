<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshOrdersSeeder extends Seeder
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

        DB::table('bond_orders')->truncate();
        DB::table('bond_ids')->truncate();
        DB::table('bond_executions')->truncate();
        DB::table('bond_execution_ids')->truncate();
        DB::table('orders')->truncate();
        DB::table('order_ids')->truncate();
        DB::table('transactions')->truncate();
        DB::table('statements')->truncate();
        DB::table('transaction_ids')->truncate();
        DB::table('dealing_sheet_ids')->truncate();
        DB::table('dealing_sheets')->truncate();
        DB::table('telescope_entries')->truncate();
        DB::table('telescope_entries_tags')->truncate();
        DB::table('simple_transactions')->truncate();
        DB::table('wallets')->truncate();
        DB::table('available_wallet_histories')->truncate();
        DB::table('payments_on_holds')->truncate();
        DB::table('payments')->truncate();
        DB::table('receipts_on_holds')->truncate();
        DB::table('receipts')->truncate();
        DB::table('bonds_on_holds')->truncate();
        DB::table('equities_on_holds')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
