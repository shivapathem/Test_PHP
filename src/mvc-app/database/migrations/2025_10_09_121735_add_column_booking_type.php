<?php

use App\Models\FacilityBooking\FacilityBookingRecurrence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnBookingType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FacilityBookingRecurrence', function (Blueprint $table) {
            $table->string('FBR_BookingType', 15)->before('FBR_FacilityID')->default(FacilityBookingRecurrence::BOOKING_TYPE_REQUEST);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FacilityBookingRecurrence', function (Blueprint $table) {
            $table->dropColumn('FBR_BookingType');
        });
    }
}
