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
        Schema::dropColumns('customer_categories', ['bonds_rate', 'shares_rate']);

        Schema::table('customer_categories', function (Blueprint $table) {
            $table->uuid('equity_scheme')->nullable();
            $table->uuid('bond_scheme')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_categories', function (Blueprint $table) {
            $table->string('bonds_rate')->nullable();
            $table->string('shares_rate')->nullable();
        });
        Schema::dropColumns('customer_categories', ['bond_scheme', 'equity_scheme']);

    }
};