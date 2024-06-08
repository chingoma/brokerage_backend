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
        Schema::table('bond_orders', function (Blueprint $table) {
            $table->string('custodian_id')->nullable();
            $table->string('has_custodian')->default('no')->nullable();
        });
        Schema::table('bond_executions', function (Blueprint $table) {
            $table->string('custodian_id')->nullable();
            $table->string('has_custodian')->default('no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bond_orders', function (Blueprint $table) {
            $table->dropColumn(['custodian_id', 'has_custodian']);
        });
        Schema::table('bond_executions', function (Blueprint $table) {
            $table->dropColumn(['custodian_id', 'has_custodian']);
        });
    }
};
