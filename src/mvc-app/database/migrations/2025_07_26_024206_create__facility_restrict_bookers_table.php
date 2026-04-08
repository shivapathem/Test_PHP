<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityRestrictBookersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityRestrictBookers', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCRB_FacilityRestrictBookerID');

            //Columns
            $table->unsignedInteger('FCRB_FacilityID');
            $table->unsignedInteger('FCRB_SchedulingTeamID');

            //Created and Updated timestamps
            $table->timestamp('FCRB_CreatedDate')->useCurrent();
            $table->timestamp('FCRB_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCRB_SchedulingTeamID')->references('schedulingTeamId')->on('schedulingTeams');
            $table->foreign('FCRB_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityRestrictBookers');
    }
}
