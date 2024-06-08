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
        Schema::create('bond_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('amount')->nullable();
            $table->string('uid')->nullable();
            $table->string('closed')->nullable();
            $table->string('request_cancel')->nullable();
            $table->string('face_value')->nullable();
            $table->string('price')->nullable();
            $table->string('client_notice')->nullable();
            $table->string('officer_notice')->nullable();
            $table->string('balance')->nullable();
            $table->string('executed')->default('0');
            $table->string('pricing_mode')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('client_id');
            $table->uuid('financial_year_id');
            $table->uuid('bond_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bond_orders');
    }
};
