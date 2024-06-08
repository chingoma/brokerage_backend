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
        //        Schema::disableForeignKeyConstraints();
        //        DB::table("permissions")->truncate();
        //        Schema::table("permissions", function ( Blueprint $table){
        ////            $table->dropColumn(['id']);
        //            $table->bigIncrements('id')->change();
        //        });
        //        DB::table("roles")->truncate();
        //
        //        Schema::table("roles", function ( Blueprint $table){
        ////            $table->dropColumn(['id']);
        //            $table->bigIncrements('id')->change();
        //        });
        //        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //        Schema::disableForeignKeyConstraints();
        //        Schema::table("permissions", function ( Blueprint $table){
        ////            $table->dropColumn(['id']);
        //            $table->uuid('id')->primary();
        //        });
        //        Schema::table("roles", function ( Blueprint $table){
        ////            $table->dropColumn(['id']);
        //            $table->uuid('id')->primary();
        //        });
        //        Schema::enableForeignKeyConstraints();
    }
};
