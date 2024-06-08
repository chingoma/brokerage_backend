<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealingSheetFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('dealing_sheet_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('path');
            $table->string('file_id');
            $table->string('extension');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('dealing_sheet_id')->references('id')->on('dealing_sheets')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealing_sheet_files');
    }
}
