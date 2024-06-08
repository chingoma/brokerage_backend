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
        Schema::create('order_reconciles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bbo_present')->default('NO')->nullable();
            $table->string('dse_present')->default('YES')->nullable();
            $table->date('trade_date')->nullable();
            $table->string('vat')->default('0')->nullable();
            $table->string('brokerage')->default('0')->nullable();
            $table->string('total_commissions')->default('0')->nullable();
            $table->string('cmsa')->default('0')->nullable();
            $table->string('dse')->default('0')->nullable();
            $table->string('closed')->nullable()->nullable();
            $table->string('fidelity')->default('0')->nullable();
            $table->string('total_fees')->default('0')->nullable();
            $table->string('cds')->default('0')->nullable();
            $table->string('commission_step_one')->default('0')->nullable();
            $table->string('commission_step_two')->default('0')->nullable();
            $table->string('commission_step_three')->default('0')->nullable();
            $table->string('payout')->default('0')->nullable();
            $table->string('volume')->nullable();
            $table->string('price')->nullable();
            $table->string('executed')->nullable();
            $table->string('balance')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('amount')->nullable();
            $table->string('slip_no')->nullable();
            $table->date('settlement_date')->nullable();

            $table->string('dse_status')->nullable();
            $table->string('dse_quantity')->nullable();
            $table->string('dse_price')->nullable();
            $table->string('dse_total')->nullable();
            $table->string('dse_trans_type')->nullable();
            $table->string('dse_instrument')->nullable();
            $table->string('dse_trade_date')->nullable();
            $table->string('dse_settlement_date')->nullable();
            $table->string('dse_exchange_date')->nullable();
            $table->string('dse_client_name')->nullable();
            $table->string('dse_sor_account')->nullable();
            $table->string('dse_bpid')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('order_id')->nullable()->references('id')->on('orders')->onUpdate('cascade');
            $table->foreignUuid('client_id')->nullable()->references('id')->on('users')->onUpdate('cascade');
            $table->foreignUuid('financial_year_id')->nullable()->references('id')->on('financial_years')->onUpdate('cascade');
            $table->foreignUuid('security_id')->nullable()->references('id')->on('securities')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_reconciles');
    }
};
