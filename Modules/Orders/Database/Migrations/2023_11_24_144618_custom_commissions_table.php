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
            $table->string('use_custom_commission')->default('no');
            $table->string('use_flat')->default('no');
            $table->string('rate_step_one')->default('0');
            $table->string('rate_step_two')->default('0');
            $table->string('rate_step_three')->default('0');
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('use_custom_commission')->default('no');
            $table->string('use_flat')->default('no');
            $table->string('rate_step_one')->default('0');
            $table->string('rate_step_two')->default('0');
            $table->string('rate_step_three')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('orders', ['use_custom_commission', 'use_flat', 'rate_step_one', 'rate_step_two', 'rate_step_three']);
        Schema::dropColumns('dealing_sheets', ['use_custom_commission', 'use_flat', 'rate_step_one', 'rate_step_two', 'rate_step_three']);
    }
};
