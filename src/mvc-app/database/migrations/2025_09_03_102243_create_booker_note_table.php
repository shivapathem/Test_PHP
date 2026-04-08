<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookerNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityBookerNoteRecurrence', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBNR_FacilityBookerNoteRecurrenceID');

            //Fields
            $table->unsignedInteger('FBNR_FacilityID');
            $table->text('FBNR_Note');
            $table->date('FBNR_SeriesStartDate');
            $table->date('FBNR_SeriesEndDate');
            $table->time('FBNR_StartTime');
            $table->time('FBNR_EndTime');
            $table->string('FBNR_RecurrenceType', 10);
            $table->unsignedInteger('FBNR_RecurrenceDayInterval')->nullable();
            $table->unsignedInteger('FBNR_RecurrenceWeekInterval')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Saturday')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Sunday')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Monday')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_TuesDay')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Wednesday')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Thursday')->nullable();
            $table->boolean('FBNR_RecurrenceWeek_Friday')->nullable();

            // Timestamps
            $table->integer('FBNR_CreatedBy');
            $table->timestamp('FBNR_CreatedDate');
            $table->integer('FBNR_UpdatedBy');
            $table->timestamp('FBNR_UpdatedDate');
            $table->softDeletes();

            //Foreign key
            $table->foreign('FBNR_FacilityID')->references('FC_FacilityID')->on('Facilities');
        });

        Schema::create('FacilityBookerNotes', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FBN_FacilityBookerNoteID');

            //Fields
            $table->unsignedInteger('FBN_FacilityBookerNoteRecurrenceID');
            $table->unsignedInteger('FBN_FacilityID');
            $table->text('FBN_Note');
            $table->dateTime('FBN_StartDateTime');
            $table->dateTime('FBN_EndDateTime');

            // Timestamps
            $table->integer('FBN_CreatedBy');
            $table->timestamp('FBN_CreatedDate');
            $table->integer('FBN_UpdatedBy');
            $table->timestamp('FBN_UpdatedDate');
            $table->softDeletes();

            //Foreign key
            $table->foreign('FBN_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FBN_FacilityBookerNoteRecurrenceID')->references('FBNR_FacilityBookerNoteRecurrenceID')->on('FacilityBookerNoteRecurrence');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityBookerNotes');
        Schema::dropIfExists('FacilityBookerNoteRecurrence');
    }
}
