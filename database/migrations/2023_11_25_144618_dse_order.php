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
            $table->string('dse_status')->default('new')->nullable();
            $table->string('dse_reference')->nullable();
        });
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('dse_status')->default('new')->nullable();
            $table->string('dse_reference')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('orders', ['dse_status', 'dse_reference']);
        Schema::dropColumns('dealing_sheets', ['dse_status', 'dse_reference']);
    }
};
