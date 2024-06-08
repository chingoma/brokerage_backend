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
        Schema::table('bonds', function (Blueprint $table) {
            $table->string('security_name');
            $table->string('market');
            $table->string('category');
            $table->string('yield_time_maturity');
        });

        Schema::table('bond_executions', function (Blueprint $table) {
            $table->string('holding_number');
            $table->string('market');
            $table->string('category');
        });

        Schema::table('bond_orders', function (Blueprint $table) {
            $table->dateTime('date')->change();
            $table->string('coupons');
            $table->string('market');
            $table->string('category');
            $table->text('overdraft_message')->nullable();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->text('overdraft_message')->nullable();
            $table->dateTime('date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('bonds', ['security_name', 'market', 'category', 'yield_time_maturity']);
        Schema::dropColumns('bond_executions', ['holding_number', 'market', 'category']);
        Schema::dropColumns('bond_orders', ['coupons', 'category', 'market', 'overdraft_message']);
        Schema::dropColumns('orders', ['overdraft_message']);
    }
};
