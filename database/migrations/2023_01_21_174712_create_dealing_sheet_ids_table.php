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
        Schema::create('dealing_sheet_ids', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('lap');
            $table->foreignUuid('foreign_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->string('uid')
                ->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealing_sheet_ids');
        Schema::table('dealing_sheets', function (Blueprint $table) {
            $table->dropColumn(['uid']);
        });
    }
};