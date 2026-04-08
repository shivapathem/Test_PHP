<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFBBookingPreviousStatusToFacilityBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FacilityBookings', function (Blueprint $table) {
            $table->string('FB_BookingPreviousStatus')->nullable()->after('FB_BookingStatus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FacilityBookings', function (Blueprint $table) {
            $table->dropColumn('FB_BookingPreviousStatus');
        });
    }
}
