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
        Schema::create('dse_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('timeout')->default(25);
            $table->string('broker_reference');
            $table->string('nida');
            $table->string('username');
            $table->string('password');
            $table->string('grant_type');
            $table->string('encoded_token');
            $table->string('client_id');
            $table->text('client_secret');
            $table->text('base_url');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('signature')->nullable();
            $table->string('expires_in')->nullable();
            $table->string('scope_in')->default('read write')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dse_settings');
    }
};
