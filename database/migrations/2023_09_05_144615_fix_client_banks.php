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
        Schema::disableForeignKeyConstraints();
        Schema::table('transactions', function (Blueprint $table) {
            $table->char('client_id', 36)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bank_name', 'bank_id');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('bank_name', 'name');
            $table->renameColumn('bic_code', 'bic');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bank_id', 'bank_name');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('name', 'bank_name');
            $table->renameColumn('bic', 'bic_code');
        });
    }
};
