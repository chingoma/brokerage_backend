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
            $table->string('custodian_id')->nullable();
            $table->string('has_custodian')->default('no')->nullable();
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('custodian_id')->nullable();
            $table->string('has_custodian')->default('no')->nullable();
        });

        Schema::table('account_settings', function (Blueprint $table) {
            $table->uuid('custodian_account')->nullable();
            $table->uuid('custodian_payee')->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('custodian_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['custodian_id', 'has_custodian']);
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->dropColumn(['custodian_id', 'has_custodian']);
        });
        Schema::table('account_settings', function (Blueprint $table) {
            $table->dropColumn(['custodian_account', 'custodian_payee']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['custodian_id']);
        });
    }
};
