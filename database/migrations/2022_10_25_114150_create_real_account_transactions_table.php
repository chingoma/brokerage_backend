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
        Schema::create('real_account_transactions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('date');
            $table->string('reconciled');
            $table->string('details');
            $table->string('action');
            $table->double('amount');
            $table->string('reference');
            $table->string('control_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('account_id')->nullable()->references('id')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('real_account_transactions');
    }
};
