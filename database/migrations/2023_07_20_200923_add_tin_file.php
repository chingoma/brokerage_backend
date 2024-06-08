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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('tin_file');
        });

        Schema::table('joints', function (Blueprint $table) {
            $table->string('tin_file');
            $table->string('current_occupation');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('profiles', ['tin_file']);
        Schema::dropColumns('joints', ['tin_file', 'current_occupation']);

    }
};
