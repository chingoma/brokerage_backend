<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJointProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('joint_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title')->nullable();
            $table->string('name')->index()->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('id_type')->nullable();
            $table->string('identity')->unique()->nullable();
            $table->string('identity_file')->nullable();
            $table->string('passport_file')->nullable();

            $table->string('country_id')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();

            $table->string('position')->nullable();
            $table->enum('employment_status', App\Helpers\Helper::employmentStatus())->nullable();
            $table->string('tin')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('current_occupation')->nullable();
            $table->string('other_employment')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('other_title')->nullable();
            $table->string('other_business')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('last_login')->nullable();
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
        Schema::dropIfExists('joint_profiles');
    }
}
