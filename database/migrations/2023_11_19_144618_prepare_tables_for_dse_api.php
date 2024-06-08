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
            $table->string('dse_account_verified')->nullable()->default('NOT VERIFIED');
            $table->string('dse_account_linkage')->nullable()->default('NOT LINKED');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('place_birth')->nullable();
        });

        Schema::table('joint_profiles', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('place_birth')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('users', ['dse_account_verified', 'dse_account_linkage']);
        Schema::dropColumns('joint_profiles', ['region', 'district', 'ward', 'place_birth']);
        Schema::dropColumns('profiles', ['region', 'district', 'ward', 'place_birth']);
    }
};
