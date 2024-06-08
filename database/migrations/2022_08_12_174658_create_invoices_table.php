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
        Schema::create('invoices', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('title');
            $table->date('date');
            $table->date('due_date');
            $table->string('payment_method_id');
            $table->string('payment_status');
            $table->text('note')->nullable();
            $table->string('tax')->nullable();
            $table->double('sub_total')->nullable()->default(0);
            $table->double('discount')->nullable()->default(0);
            $table->double('tax_value')->nullable()->default(0);
            $table->double('total')->nullable()->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('officer_id')->nullable()->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('client_id')->nullable()->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
