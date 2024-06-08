<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('price')->nullable();
            $table->string('bond_number')->nullable();
            $table->string('bond_isin')->nullable();
            $table->string('bond_coupon')->nullable();
            $table->string('bond_tenure')->nullable();
            $table->string('bond_issue_date')->nullable();
            $table->string('bond_maturity_date')->nullable();
            $table->string('bond_issued_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonds');
    }
};
