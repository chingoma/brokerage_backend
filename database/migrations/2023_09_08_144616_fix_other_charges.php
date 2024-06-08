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
            $table->char('other_charges', 36);
        });
        Schema::table('account_settings', function (Blueprint $table) {
            $table->char('other_charges', 36);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('dealing_sheets', ['other_charges']);
        Schema::dropColumns('account_settings', ['other_charges']);
    }
};
