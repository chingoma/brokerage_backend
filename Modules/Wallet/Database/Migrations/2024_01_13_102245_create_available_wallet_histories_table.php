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
        Schema::create('available_wallet_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index('available_wallet_histories_user_id');
            $table->uuid('model_id')->index('available_wallet_histories_model_id');
            $table->string('amount')->default(0.0);
            $table->string('action');
            $table->string('category');
            $table->string('description', 200);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_wallet_histories');
    }
};
