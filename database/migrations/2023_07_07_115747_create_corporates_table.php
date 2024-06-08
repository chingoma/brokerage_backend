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
        Schema::create('corporates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('corporate_type', \App\Helpers\Helper::corporateTypes())->nullable();
            $table->string('other_corporate_type')->nullable();
            $table->string('corporate_name')->nullable();
            $table->string('certificate_incorporation')->nullable();
            $table->string('board_resolution')->nullable();
            $table->string('tin_certificate')->nullable();
            $table->string('corporate_telephone')->nullable();
            $table->string('corporate_email')->nullable();
            $table->string('corporate_trade_name')->nullable();
            $table->string('corporate_address')->nullable();
            $table->string('corporate_building')->nullable();
            $table->string('corporate_reg_number')->nullable();
            $table->string('corporate_tin')->nullable();
            $table->uuid('user_id')->nullable();
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
        Schema::dropIfExists('corporates');
    }
};
