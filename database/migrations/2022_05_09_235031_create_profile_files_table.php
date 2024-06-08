<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('profile_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('path');
            $table->string('file_id');
            $table->string('extension');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('document_type_id')->references('id')->on('document_types')->onUpdate('cascade');
            $table->foreignUuid('profile_id')->references('id')->on('profiles')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profile_files');
    }
}
