<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityAvailability', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCA_FacilityAvailabilityID');
            $table->unsignedInteger('FCA_FacilityID');
            foreach(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $table->time('FCA_FacilityTimeFrom_' . $day);
                $table->time('FCA_FacilityTimeTo_' . $day);
                $table->boolean('FCA_FacilityTimeAvailability_' . $day)->default(1);
            }

            //Created and Updated timestamps
            $table->timestamp('FCA_CreatedDate')->useCurrent();
            $table->timestamp('FCA_UpdatedDate')->useCurrent();
            //Foreign key
            $table->foreign('FCA_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityAvailability');
    }
}
