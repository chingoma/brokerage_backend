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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id')->nullable();
            $table->string('name')->default('Whatsapp');
            $table->string('api_version')->default('v17.0');
            $table->text('webhook_token');
            $table->text('access_token')->nullable();
            $table->string('w_business_id')->nullable();
            $table->string('whatsapp_id')->nullable();
            $table->string('base_url')->default('https://graph.facebook.com');
            $table->string('callback_url')->nullable();
            $table->string('status')->default('inactive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
