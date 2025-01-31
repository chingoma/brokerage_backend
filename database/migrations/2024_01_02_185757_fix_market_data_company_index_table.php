<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('market_data', function (Blueprint $table) {
            $table->string('company_id')->index('market_data_company_id')->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //        Schema::table('market_data', function (Blueprint $table) {
        //            $table->string("market_data_company_id")->change();
        //        });
    }
};
