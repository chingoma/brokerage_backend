<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('order_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('dealing_sheet_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('bond_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('bond_execution_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('user_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });
        Schema::table('asset_ids', function (Blueprint $table) {
            $table->string('year')->default('2023');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropColumns('transaction_ids', ['year']);
        Schema::dropColumns('order_ids', ['year']);
        Schema::dropColumns('dealing_sheet_ids', ['year']);
        Schema::dropColumns('bond_ids', ['year']);
        Schema::dropColumns('bond_execution_ids', ['year']);
        Schema::dropColumns('user_ids', ['year']);
        Schema::dropColumns('asset_ids', ['year']);
    }
};
