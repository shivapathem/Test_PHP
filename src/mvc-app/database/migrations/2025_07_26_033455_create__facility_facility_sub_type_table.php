<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityFacilitySubTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityFacilitySubTypes', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCST_FacilityFacilitySubTypeID');
            $table->unsignedInteger('FCST_FacilityID');
            $table->unsignedInteger('FCST_FacilitySubTypeID');
            $table->boolean('FCST_PrimarySubType')->default(0);

            //Created and Updated timestamps
            $table->timestamp('FCST_CreatedDate')->useCurrent();
            $table->timestamp('FCST_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCST_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FCST_FacilitySubTypeID')->references('FST_FacilitySubTypeID')->on('FacilitySubTypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityFacilitySubTypes');
    }
}
