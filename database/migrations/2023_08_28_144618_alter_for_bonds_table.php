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
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('slip_no', 200)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('reference')->change();
            $table->string('category')->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
