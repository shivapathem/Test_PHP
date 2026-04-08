<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Locations', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('LN_LocationID');

            // Columns
            $table->string('LN_Location', 50)->unique();

            // Timestamps
            $table->integer('LN_CreatedBy');
            $table->timestamp('LN_CreatedDate');
            $table->integer('LN_UpdatedBy');
            $table->timestamp('LN_UpdatedDate');
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
        Schema::dropIfExists('Locations');
    }
}
