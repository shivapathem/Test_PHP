<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkedSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityBookingRecurrenceLinkedRecurrences', function (Blueprint $table) {
             // Primary Key
            $table->increments('FBRLR_FacilityBookingRecurrenceLinkedRecurrenceID');
            // Linked series
            $table->unsignedInteger('FBRLR_FacilityBookingRecurrenceID');
            $table->unsignedInteger('FBRLR_LinkedFacilityBookingRecurrenceID');
            $table->string('FBRLR_LinkType', 50);
            $table->integer('FBRLR_Created_BY');
            $table->dateTime('FBRLR_Created_ON')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityBookingRecurrenceLinkedRecurrences');
    }
}
