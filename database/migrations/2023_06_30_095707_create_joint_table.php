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
        Schema::create('joints', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('id_type')->nullable();
            $table->string('identity')->nullable();
            $table->string('identity_file')->nullable();
            $table->string('passport_file')->nullable();

            $table->string('country_id')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();

            $table->enum('employment_status', App\Helpers\Helper::employmentStatus())->nullable();
            $table->string('tin')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('present_occupation')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('other_title')->nullable();
            $table->string('other_business')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignUuid('user_id')->references('id')->on('users')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('joints');
    }
};
