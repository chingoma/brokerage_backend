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
        Schema::table('statements', function (Blueprint $table) {
            $table->uuid('trans_id')->default('new')->nullable();
            $table->string('trans_reference')->nullable();
            $table->string('trans_category')->nullable();
            $table->string('order_type')->nullable();
            $table->uuid('order_id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('statements', ['trans_id', 'trans_reference', 'trans_category', 'order_id', 'order_type']);
    }
};
