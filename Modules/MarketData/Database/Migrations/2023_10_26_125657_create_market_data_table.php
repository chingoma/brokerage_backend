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
        Schema::create('market_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('symbol');
            $table->string('open');
            $table->string('prev_close');
            $table->string('close');
            $table->string('high');
            $table->string('low');
            $table->string('change');
            $table->string('turn_over');
            $table->string('deals');
            $table->string('out_standing_bid');
            $table->string('out_standing_offer');
            $table->string('volume');
            $table->string('mcap');
            $table->date('date');
            $table->date('system_date');
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
        Schema::dropIfExists('market_data');
    }
};
