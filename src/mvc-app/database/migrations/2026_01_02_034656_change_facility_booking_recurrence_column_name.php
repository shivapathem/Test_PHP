<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('FacilityBookingRecurrence', function (Blueprint $table) {
            // Rename column
            $table->renameColumn('FBR_RecurrenceWeek_TuesDay', 'FBR_RecurrenceWeek_Tuesday');
        });
        Schema::table('FacilityBookerNoteRecurrence', function (Blueprint $table) {
            // Rename column
            $table->renameColumn('FBNR_RecurrenceWeek_TuesDay', 'FBNR_RecurrenceWeek_Tuesday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('FacilityBookingRecurrence', function (Blueprint $table) {
            // Rename column
            $table->renameColumn('FBR_RecurrenceWeek_Tuesday', 'FBR_RecurrenceWeek_TuesDay');
        });
        Schema::table('FacilityBookerNoteRecurrence', function (Blueprint $table) {
            // Rename column
            $table->renameColumn('FBNR_RecurrenceWeek_Tuesday', 'FBNR_RecurrenceWeek_TuesDay');
        });
    }
};
