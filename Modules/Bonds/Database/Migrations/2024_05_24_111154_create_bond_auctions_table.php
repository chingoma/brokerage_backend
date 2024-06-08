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
        Schema::create('bond_auctions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date("date");
            $table->string("auction_number")->nullable();
            $table->string("auction_title")->nullable();
            $table->string("coupon")->nullable();
            $table->string("coupon_frequency")->nullable();
            $table->string("auction_date")->nullable();
            $table->string("maturity_date")->nullable();
            $table->string("price")->nullable();
            $table->string("highest_bid")->nullable();
            $table->string("lowest_bid")->nullable();
            $table->string("yield")->nullable();
            $table->string("calculated_yield")->nullable();
            $table->string("yield_differential")->nullable();
            $table->string("calculated_price")->nullable();
            $table->string("price_differential")->nullable();
            $table->string("bond_id")->nullable();
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bond_auctions');
    }
};
