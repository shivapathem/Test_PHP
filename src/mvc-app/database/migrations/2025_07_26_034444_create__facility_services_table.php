<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityServices', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCSR_FacilityServiceID');
            $table->unsignedInteger('FCSR_FacilityID');
            $table->unsignedInteger('FCSR_ServiceID');

            //Created and Updated timestamps
            $table->timestamp('FCSR_CreatedDate')->useCurrent();
            $table->timestamp('FCSR_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCSR_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FCSR_ServiceID')->references('SR_ServiceID')->on('Services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityServices');
    }
}
