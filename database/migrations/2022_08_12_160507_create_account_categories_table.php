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
        Schema::create('account_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['payment', 'receipt', 'products', 'services', 'income', 'expense', 'invoice']);
            $table->string('color')->default('FFFFFF')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('debit_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('credit_account')->nullable()->references('id')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_categories');
    }
};
