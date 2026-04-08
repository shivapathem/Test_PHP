<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityBookingRecurrence', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBR_FacilityBookingRecurrenceID');

            //Fields
            $table->unsignedInteger('FBR_FacilityID');
            $table->date('FBR_SeriesStartDate');
            $table->date('FBR_SeriesEndDate');
            $table->time('FBR_StartTime');
            $table->time('FBR_EndTime');
            $table->string('FBR_RecurrenceType', 10);
            $table->unsignedInteger('FBR_RecurrenceDayInterval')->nullable();
            $table->unsignedInteger('FBR_RecurrenceWeekInterval')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Saturday')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Sunday')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Monday')->nullable();
            $table->boolean('FBR_RecurrenceWeek_TuesDay')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Wednesday')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Thursday')->nullable();
            $table->boolean('FBR_RecurrenceWeek_Friday')->nullable();
            $table->string('FBR_BookingTitle');
            $table->unsignedInteger('FBR_FacilitySubTypeID');
            $table->string('FBR_Private', 15);
            $table->string('FBR_CustomerType', 10);
            $table->unsignedInteger('FBR_ExternalCustomerID')->nullable();
            $table->string('FBR_ContactName', 50)->nullable();
            $table->string('FBR_ContactTelephone', 20)->nullable();
            $table->string('FBR_ContactEmail', 50)->nullable();
            $table->string('FBR_RequestorName', 50);
            $table->string('FBR_RequestorDetail', 50);
            $table->string('FBR_RequestorNote')->nullable();
            $table->string('FBR_SchedulerNote')->nullable();

            // Timestamps
            $table->integer('FBR_CreatedBy');
            $table->timestamp('FBR_CreatedDate');
            $table->integer('FBR_UpdatedBy');
            $table->timestamp('FBR_UpdatedDate');
            $table->softDeletes();

            //Foreign key
            $table->foreign('FBR_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });

        //Facility Recurrence action
        Schema::create('FacilityBookingRecurrenceActions', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBRA_FacilityBookingRecurrenceActionID');

            $table->unsignedInteger('FBRA_FacilityBookingRecurrenceID');
            $table->unsignedInteger('FBRA_ActionID');
            $table->time('FBRA_ActionStartTime');
            $table->time('FBRA_ActionEndTime');

            // Timestamps
            $table->timestamp('FBRA_CreatedDate')->useCurrent();
        });

        //Facility Recurrence Linked facility
        Schema::create('FacilityBookingRecurrenceLinkedFacility', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBRLF_FacilityBookingRecurrenceLinkedFacilityID');

            $table->unsignedInteger('FBRLF_FacilityBookingRecurrenceID');
            $table->unsignedInteger('FBRLF_FacilityID');

            // Timestamps
            $table->timestamp('FBRLF_CreatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FBRLF_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });

        Schema::create('FacilityBookings', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FB_FacilityBookingID');

            //Fields
            $table->unsignedInteger('FB_FacilityBookingRecurrenceID');
            $table->unsignedInteger('FB_FacilityID');
            $table->timestamp('FB_BookingStartDateTime');
            $table->timestamp('FB_BookingEndDateTime');
            $table->string('FB_BookingTitle');
            $table->unsignedInteger('FB_FacilitySubTypeID')->nullable();
            $table->string('FB_Private', 15);
            $table->string('FB_CustomerType', 10);
            $table->unsignedInteger('FB_ExternalCustomerID')->nullable();
            $table->string('FB_ContactName', 50)->nullable();
            $table->string('FB_ContactTelephone', 20)->nullable();
            $table->string('FB_ContactEmail', 50)->nullable();
            $table->string('FB_RequestorName', 50);
            $table->string('FB_RequestorDetail', 50);
            $table->string('FB_RequestorNote')->nullable();
            $table->string('FB_SchedulerNote')->nullable();
            $table->string('FB_BookingStatus', 15);
            $table->date('FB_BookingCancelDate')->nullable();
            $table->date('FB_BookingConfirmDate')->nullable();
            $table->date('FB_BookingDeclineDate')->nullable();
            $table->string('FB_BookingDeclineReason')->nullable();

            // Timestamps
            $table->integer('FB_CreatedBy');
            $table->timestamp('FB_CreatedDate');
            $table->integer('FB_UpdatedBy');
            $table->timestamp('FB_UpdatedDate');

            //Foreign key
            $table->foreign('FB_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FB_FacilityBookingRecurrenceID')->references('FBR_FacilityBookingRecurrenceID')->on('FacilityBookingRecurrence');
        });

        //Facility action
        Schema::create('FacilityBookingActions', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBA_FacilityBookingActionID');

            $table->unsignedInteger('FBA_FacilityBookingID');
            $table->unsignedInteger('FBA_ActionID');
            $table->time('FBA_ActionStartTime');
            $table->time('FBA_ActionEndTime');

            // Timestamps
            $table->timestamp('FBA_CreatedDate')->useCurrent();
        });

        //Facility booking Linked Facility booking
        Schema::create('FacilityBookingLinkedFacilityBookings', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBLFB_FacilityBookingLinkedFacilityBookingID');

            $table->unsignedInteger('FBLFB_FacilityBookingID');
            $table->unsignedInteger('FBLFB_LinkedFacilityBookingID');

            // Timestamps
            $table->timestamp('FBLFB_CreatedDate')->useCurrent();
            //Foreign key
            $table->foreign('FBLFB_LinkedFacilityBookingID')->references('FB_FacilityBookingID')->on('FacilityBookings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityBookingLinkedFacilityBookings');
        Schema::dropIfExists('FacilityBookings');
        Schema::dropIfExists('FacilityBookingActions');
        Schema::dropIfExists('FacilityBookingRecurrence');
        Schema::dropIfExists('FacilityBookingRecurrenceActions');
        Schema::dropIfExists('FacilityBookingRecurrenceLinkedFacility');
    }
}
