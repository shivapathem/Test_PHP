<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityLocations', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCL_FacilityLocationID');

            //Columns
            $table->unsignedInteger('FCL_FacilityID');
            $table->unsignedInteger('FCL_InternalLocationID')->nullable();
            $table->string('FCL_BuildingNumberName_EXTUK', 50)->nullable();
            $table->string('FCL_Street_EXTUK', 50)->nullable();
            $table->string('FCL_City_EXTUK', 50)->nullable();
            $table->string('FCL_County_EXTUK', 50)->nullable();
            $table->string('FCL_Postcode_EXTUK', 50)->nullable();
            $table->string('FCL_Country_EXTUK', 50)->nullable();
            $table->string('FCL_CompleteAddress_EXTINT', 255)->nullable();

            //Created and Updated timestamps
            $table->timestamp('FCL_CreatedDate')->useCurrent();
            $table->timestamp('FCL_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCL_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FCL_InternalLocationID')->references('LN_LocationID')->on('Locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityLocations');
    }
}
