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
        Schema::dropColumns('equity_schemes', ['is_default', 'is_flat']);

        Schema::table('equity_schemes', function (Blueprint $table) {
            $table->string('broker_fee')->nullable();
            $table->string('mode');
            $table->string('flat_rate')->nullable();
            $table->string('step_one')->nullable();
            $table->string('step_two')->nullable();
            $table->string('step_three')->nullable();
            $table->string('dse_fee')->nullable();
            $table->string('csdr_fee')->nullable();
            $table->string('cmsa_fee')->nullable();
            $table->string('fidelity_fee')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equity_schemes', function (Blueprint $table) {
            $table->string('is_default')->default('no');
            $table->string('is_flat')->default('no');
        });
        Schema::dropColumns('equity_schemes', ['broker_fee', 'mode', 'flat_rate', 'step_two', 'step_three', 'dse_fee', 'csdr_fee', 'cmsa_fee', 'fidelity_fee', 'deleted_at']);

    }
};
