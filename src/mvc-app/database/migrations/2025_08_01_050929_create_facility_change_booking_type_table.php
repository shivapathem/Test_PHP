<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityChangeBookingTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityChangeBookingTypes', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCBT_FacilityChangeBookingTypeID');
            $table->unsignedInteger('FCBT_FacilityID');
            $table->date('FCBT_FacilityChangeBookingTypeStartDate');
            $table->date('FCBT_FacilityChangeBookingTypeEndDate');
            foreach(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $table->time('FCBT_FacilityChangeBookingTypeTimeFrom_' . $day);
                $table->time('FCBT_FacilityChangeBookingTypeTimeTo_' . $day);
            }

            //Created and Updated timestamps
            $table->timestamp('FCBT_CreatedDate')->useCurrent();
            $table->timestamp('FCBT_UpdatedDate')->useCurrent();
            //Foreign key
            $table->foreign('FCBT_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityChangeBookingTypes');
    }
}
