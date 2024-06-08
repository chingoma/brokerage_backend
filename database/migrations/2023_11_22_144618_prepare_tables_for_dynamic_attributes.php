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
        Schema::table('users', function (Blueprint $table) {
            $table->json('values')->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->json('values')->nullable();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->json('values')->nullable();
        });

        Schema::table('joint_profiles', function (Blueprint $table) {
            $table->json('values')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('users', ['values']);
        Schema::dropColumns('transactions', ['values']);
        Schema::dropColumns('joint_profiles', ['values']);
        Schema::dropColumns('profiles', ['values']);
    }
};
