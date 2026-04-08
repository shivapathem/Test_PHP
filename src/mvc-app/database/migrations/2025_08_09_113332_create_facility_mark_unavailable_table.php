<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityMarkUnavailableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityMarkUnavailable', function (Blueprint $table) {
            // Primary Key
            $table->increments('FMU_FacilityMarkUnavailableID');
            $table->unsignedInteger('FMU_FacilityID');
            $table->date('FMU_FacilityMarkUnavailableStartDate');
            $table->date('FMU_FacilityMarkUnavailableEndDate')->nullable();
            foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $table->time('FMU_FacilityMarkUnavailableTimeFrom_' . $day);
                $table->time('FMU_FacilityMarkUnavailableTimeTo_' . $day);
            }

            //Created and Updated timestamps
            $table->timestamp('FMU_CreatedDate')->useCurrent();
            $table->timestamp('FMU_UpdatedDate')->useCurrent();
            //Foreign key
            $table->foreign('FMU_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityMarkUnavailable');
    }
}
