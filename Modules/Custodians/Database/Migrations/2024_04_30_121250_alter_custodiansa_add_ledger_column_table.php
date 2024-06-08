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
        Schema::table('custodians', function (Blueprint $table) {
            $table->string('contact_person')->nullable();
            $table->string('ledger');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("custodians", function (Blueprint $table){
            $table->dropColumn(['ledger','contact_person']);
        });
    }
};
