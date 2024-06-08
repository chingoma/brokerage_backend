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
        Schema::create('bonds_on_holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index('bonds_on_holds_user_id');
            $table->uuid('bond_id')->index('bonds_on_holds_bond_id');
            $table->string('amount')->default(0.0);
            $table->string('balance')->default(0.0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonds_on_holds');
    }
};
