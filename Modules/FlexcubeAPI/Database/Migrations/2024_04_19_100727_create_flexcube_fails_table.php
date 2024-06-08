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
        Schema::create('flexcube_fails', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("brokerlink_reference")->index();
            $table->string("error_level")->index();
            $table->string("description");
            $table->date("posting_date")->index();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexcube_fails');
    }
};
