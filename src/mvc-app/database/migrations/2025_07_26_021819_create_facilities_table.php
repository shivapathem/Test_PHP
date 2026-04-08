<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('Facilities', function (Blueprint $table) {
            // Primary Key
            $table->increments('FC_FacilityID');

            // Basic string columns
            $table->string('FC_FacilityName', 255)->unique();
            $table->date('FC_ActiveFrom');
            $table->unsignedInteger('FC_AreaOwnerID');
            $table->string('FC_ProviderType', 50);
            $table->string('FC_Contact', 50);
            $table->string('FC_ProviderName', 255);
            $table->string('FC_LocationNote', 255);
            $table->unsignedInteger('FC_FacilityCapacity');
            $table->string('FC_Accessible', 10);
            $table->string('FC_AccessibilityNote', 255);
            $table->string('FC_DefaultBookingType', 20);
            $table->string('FC_MakeAllBookingsPrivate', 20);
            $table->boolean('FC_AllowBookingRequest');
            $table->boolean('FC_OneOffBookingsOnly');
            $table->unsignedInteger('FC_AllowSelfBookingFrom');
            $table->unsignedInteger('FC_AllowSelfBookingTo');
            $table->string('FC_PopupNote', 255);
            $table->string('FC_FacilityNote', 255);
            $table->unsignedInteger('FC_FacilityTypeID');

            // Created and Updated timestamps
            $table->unsignedInteger('FC_CreatedBy');
            $table->unsignedInteger('FC_UpdatedBy');
            $table->timestamp('FC_CreatedDate');
            $table->timestamp('FC_UpdatedDate');
            $table->timestamp('FC_ArchivedDate')->nullable();
            $table->softDeletes();

            //foreign key
            $table->foreign('FC_AreaOwnerID')->references('DivisionID')->on('Divisions');
            $table->foreign('FC_FacilityTypeID')->references('FT_FacilityTypeID')->on('FacilityTypes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Facilities');
    }
}
