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
        Schema::create('bond_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('trade_date');
            $table->string('vat')->default('0');
            $table->string('uid');
            $table->string('brokerage')->default('0');
            $table->string('total_commissions')->default('0');
            $table->string('cmsa')->default('0');
            $table->string('dse')->default('0');
            $table->string('closed')->nullable();
            $table->string('fidelity')->default('0');
            $table->string('total_fees')->default('0');
            $table->string('cds')->default('0');
            $table->string('brokerage_rate')->default('0');
            $table->string('payout')->default('0');
            $table->string('face_value')->nullable();
            $table->string('price')->nullable();
            $table->string('executed')->nullable();
            $table->string('balance')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('amount')->nullable();
            $table->text('slip_no')->nullable();
            $table->date('settlement_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('client_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreignUuid('financial_year_id')->references('id')->on('financial_years')->onUpdate('cascade');
            $table->foreignUuid('bond_id')->nullable()->references('id')->on('bonds')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bond_executions');
    }
};
