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
        //        Schema::table('statements', function (Blueprint $table) {
        //            $table->index('auto');
        //            $table->bigIncrements('auto')->unsigned();
        //        });
        //        Schema::table('transactions', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
        //        Schema::table('orders', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
        //        Schema::table('bond_orders', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
        //        Schema::table('dealing_sheets', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
        //        Schema::table('bond_executions', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
        //        Schema::table('users', function (Blueprint $table) {
        //            $table->bigIncrements('auto')->index()->unsigned();
        //        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //        Schema::dropColumns('statements',"auto");
        //        Schema::dropColumns('transactions',"auto");
        //        Schema::dropColumns('orders',"auto");
        //        Schema::dropColumns('bond_orders',"auto");
        //        Schema::dropColumns('dealing_sheets',"auto");
        //        Schema::dropColumns('bond_executions',"auto");
        //        Schema::dropColumns('users',"auto");
    }
};
