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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('vat')->default('0');
            $table->string('brokerage')->default('0');
            $table->string('total_commissions')->default('0');
            $table->string('cmsa')->default('0');
            $table->string('dse')->default('0');
            $table->string('fidelity')->default('0');
            $table->string('total_fees')->default('0');
            $table->string('cds')->default('0');
            $table->string('brokerage_rate')->default('0');
            $table->string('payout')->default('0');
            $table->string('commission_step_one')->default('0');
            $table->string('commission_step_two')->default('0');
            $table->string('commission_step_three')->default('0');
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('brokerage_rate')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('orders', ['commission_step_three', 'commission_step_two', 'commission_step_one', 'payout', 'brokerage_rate', 'cds', 'total_fees', 'fidelity', 'dse', 'cmsa', 'total_commissions', 'brokerage', 'vat']);
        Schema::dropColumns('dealing_sheets', ['brokerage_rate']);
    }
};
