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
        Schema::create('bond_schemes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('mode');
            $table->string('broker_fee')->nullable();
            $table->string('flat_rate')->nullable();
            $table->string('step_one')->nullable();
            $table->string('step_two')->nullable();
            $table->string('dse_fee')->nullable();
            $table->string('csdr_fee')->nullable();
            $table->string('cmsa_fee')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bond_schemes');
    }
};
