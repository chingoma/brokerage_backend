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
            $table->string('bot_cds_number')->nullable();
            $table->string('bot_security_number')->nullable();
        });

        Schema::table('bond_executions', function (Blueprint $table) {
            $table->string('bot_security_number')->nullable();
            $table->string('bot_cds_number')->nullable();
            $table->string('auction_date')->nullable();
            $table->string('auction_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('users', ['bot_cds_number','bot_security_number']);
        Schema::dropColumns('bond_executions', ['auction_date','auction_number','bot_cds_number','bot_security_number']);
    }
};
