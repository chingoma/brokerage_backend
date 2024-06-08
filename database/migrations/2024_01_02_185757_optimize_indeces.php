<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('reference')->index('transactions_reference')->change();
            $table->string('transaction_date')->index('transactions_transaction_date')->change();
        });

        Schema::table('bond_orders', function (Blueprint $table) {
            $table->string('client_id')->index('bond_orders_client_id')->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //        Schema::table('transactions', function (Blueprint $table) {
        //            $table->dropIndex("transactions_client_id");
        //            $table->dropIndex("transactions_reference");
        //            $table->dropIndex("transactions_transaction_date");
        //        });
        //
        //        Schema::table('bond_orders', function (Blueprint $table) {
        //            $table->dropIndex("bond_orders_client_id");
        //        });

    }
};
