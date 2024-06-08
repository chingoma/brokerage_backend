<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('amount')->nullable();
            $table->string('closed')->nullable();
            $table->string('request_cancel')->nullable();
            $table->string('volume')->nullable();
            $table->string('price')->nullable();
            $table->string('client_notice')->nullable();
            $table->string('officer_notice')->nullable();
            $table->string('balance')->nullable();
            $table->string('executed')->default('0');
            $table->string('pricing_mode')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('client_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreignUuid('financial_year_id')->references('id')->on('financial_years')->onUpdate('cascade');
            $table->foreignUuid('security_id')->references('id')->on('securities')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
