<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cash_account')->nullable();
            $table->string('customer_liability_account')->nullable();
            $table->string('customer_cash_account')->nullable();
            $table->string('order_liability_account')->nullable();
            $table->string('order_cash_account')->nullable();
            $table->string('order_revenue_account')->nullable();
            $table->string('cmsa_fee_account')->nullable();
            $table->string('fidelity_fee_account')->nullable();
            $table->string('dse_fee_account')->nullable();
            $table->string('cds_fee_account')->nullable();
            $table->string('vat_account')->nullable();
            $table->string('receipt_expense_account')->nullable();
            $table->string('receipt_cash_account')->nullable();
            $table->string('bill_liability_account')->nullable();
            $table->string('bill_cash_account')->nullable();
            $table->string('bill_expense_account')->nullable();
            $table->foreignUuid('cmsa_payee_account')->nullable();
            $table->foreignUuid('fidelity_payee_account')->nullable();
            $table->foreignUuid('dse_payee_account')->nullable();
            $table->foreignUuid('cds_payee_account')->nullable();
            $table->foreignUuid('vat_payee_account')->nullable();
            $table->foreignUuid('business_id')->references('id')->on('businesses')->onUpdate('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_settings');
    }
}
