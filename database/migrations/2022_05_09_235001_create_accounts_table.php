<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('nature');
            $table->string('code');
            $table->string('account_number')->nullable();
            $table->string('description');
            $table->string('increase');
            $table->string('decrease');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('business_id')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('branch_id')->references('id')->on('branches')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignUuid('class_id')->references('id')->on('account_classes')->onUpdate('restrict');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
