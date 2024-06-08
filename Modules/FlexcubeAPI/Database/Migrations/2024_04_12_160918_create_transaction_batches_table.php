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
        Schema::create('transaction_batches', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->date("date");
            $table->string("batch_prefix");
            $table->string("batch_reference")->nullable();
            $table->string("batch_number");
            $table->string("batch");
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_batches');
    }
};
