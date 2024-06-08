<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketCustomReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('market_custom_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status')->default('draft');
            $table->dateTime('schedule')->nullable();
            $table->string('title');
            $table->text('description');
            $table->integer('recipients');
            $table->date('launched')->nullable();
            $table->text('file_name')->nullable();
            $table->string('file_ext');
            $table->string('file_path');
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
        Schema::dropIfExists('market_custom_reports');
    }
}
