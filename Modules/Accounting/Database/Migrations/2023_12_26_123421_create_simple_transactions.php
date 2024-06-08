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
        Schema::create('simple_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('trans_id')->default('new')->nullable();
            $table->uuid('order_id')->nullable();
            $table->string('trans_reference')->nullable();
            $table->string('trans_category')->nullable();
            $table->string('order_type')->nullable();
            $table->dateTime('date');
            $table->string('type');
            $table->string('category');
            $table->string('reference');
            $table->string('particulars');
            $table->string('quantity');
            $table->string('price');
            $table->string('debit');
            $table->string('credit');
            $table->string('status')->default('pending');
            $table->string('amount');
            $table->string('action');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_transactions');
    }
};
