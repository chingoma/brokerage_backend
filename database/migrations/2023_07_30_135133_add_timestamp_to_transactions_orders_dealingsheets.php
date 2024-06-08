<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('updated_by')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('created_by')->nullable();
            $table->dateTime('transaction_date')->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('date')->change();
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->dateTime('trade_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('transactions', ['updated_by', 'reviewed_by', 'approved_by', 'created_by']);

    }
};
