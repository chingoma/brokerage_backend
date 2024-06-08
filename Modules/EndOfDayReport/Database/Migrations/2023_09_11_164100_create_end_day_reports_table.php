<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEndDayReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('end_day_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('trade_status');
            $table->string('trade_status_description')->nullable();
            $table->string('finance_status');
            $table->string('finance_status_description')->nullable();
            $table->string('process_status');
            $table->string('process_status_description')->nullable();
            $table->string('system_status');
            $table->string('system_status_description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('end_day_reports');
    }
}
