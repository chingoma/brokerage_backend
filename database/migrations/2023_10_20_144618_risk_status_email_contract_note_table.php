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
            $table->string('risk_status')->nullable();
        });

        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('email_sent')->default('no')->nullable();
        });

        Schema::table('bond_executions', function (Blueprint $table) {
            $table->string('email_sent')->default('no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('users', ['risk_status']);
        Schema::dropColumns('dealing_sheets', ['email_sent']);
        Schema::dropColumns('bond_executions', ['email_sent']);
    }
};
