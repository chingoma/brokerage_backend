<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelescopeEntriesTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->string('tag')->index('telescope_entries_tags_tag_index');
            $table->index(['entry_uuid', 'tag'], 'telescope_entries_tags_entry_uuid_tag_index');
            $table->foreignUuid('entry_uuid');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telescope_entries_tags');
    }
}
