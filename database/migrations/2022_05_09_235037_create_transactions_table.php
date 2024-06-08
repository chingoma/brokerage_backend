<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->string('amount');
            $table->string('cheque_number')->nullable();
            $table->string('withdraw_account')->default('no');
            $table->string('cash_account')->default('no');
            $table->string('payment_type')->nullable();
            $table->string('expense_type')->nullable();
            $table->string('receipt_type')->nullable();
            $table->date('transaction_date');
            $table->string('debit');
            $table->string('credit');
            $table->string('external_reference')->nullable();
            $table->string('reference')->nullable();
            $table->string('slip_number')->nullable();
            $table->enum('category', ['payment', 'receipt', 'order', 'voucher', 'journal', 'payroll', 'bill', 'expense', 'sale', 'deposit', 'withdraw'])->default('order');
            $table->enum('action', ['debit', 'credit']);
            $table->string('description');
            $table->string('is_journal')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('Pending');
            $table->enum('customer_action', ['deposit', 'withdraw'])->nullable();
            $table->enum('vat_type', ['input', 'output'])->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('account_category_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->uuid('class_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->uuid('financial_year_id')->nullable();
            $table->uuid('order_id')->nullable();
            $table->uuid('payment_method_id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
