<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilitySubTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilitySubTypes', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FST_FacilitySubTypeID');

            // Columns
            $table->string('FST_FacilitySubType', 50);
            $table->unsignedInteger('FST_FacilityTypeID');

            // Timestamps
            $table->timestamp('FST_CreatedDate');
            $table->timestamp('FST_UpdatedDate');
            $table->softDeletes();

            //foreign key
             $table->foreign('FST_FacilityTypeID')->references('FT_FacilityTypeID')->on('FacilityTypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilitySubTypes');
    }
}
