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
        //        Schema::create('account_settings', function (Blueprint $table) {
        //            $table->uuid("id")->primary();
        //            $table->foreignUuid('cash_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('customer_liability_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('customer_cash_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('order_liability_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('order_cash_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('order_revenue_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('cmsa_fee_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('fidelity_fee_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('dse_fee_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('cds_fee_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('vat_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('receipt_expense_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('receipt_cash_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('bill_liability_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('bill_cash_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('bill_expense_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade');
        //            $table->foreignUuid('cmsa_payee_account')->nullable()->references('id')->on('users')->onUpdate('cascade');
        //            $table->foreignUuid('fidelity_payee_account')->nullable()->references('id')->on('users')->onUpdate('cascade');
        //            $table->foreignUuid('dse_payee_account')->nullable()->references('id')->on('users')->onUpdate('cascade');
        //            $table->foreignUuid('cds_payee_account')->nullable()->references('id')->on('users')->onUpdate('cascade');
        //            $table->foreignUuid('vat_payee_account')->nullable()->references('id')->on('users')->onUpdate('cascade');
        //            $table->foreignUuid('business_id')->references('id')->on('businesses')->onUpdate('cascade');
        //            $table->softDeletes();
        //            $table->timestamps();
        //        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //        Schema::dropIfExists('account_settings');
    }
};
