<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('transaction_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('path');
            $table->string('file_id');
            $table->string('extension');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('business_id', 'transaction_files_business_id_foreign')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('document_type_id', 'transaction_files_document_type_id_foreign')->references('id')->on('document_types')->onUpdate('cascade');
            $table->foreignUuid('transaction_id', 'transaction_files_transaction_id_foreign')->references('id')->on('transactions')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_files');
    }
}
