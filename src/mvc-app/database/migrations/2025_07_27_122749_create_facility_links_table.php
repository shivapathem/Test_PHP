<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityLinks', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCLK_FacilityLinkID');
            $table->unsignedInteger('FCLK_FacilityID');
            $table->unsignedInteger('FCLK_LinkedFacilityID');
            $table->boolean('FCLK_Mandatory')->default(0);

            //Created and Updated timestamps
            $table->timestamp('FCLK_CreatedDate')->useCurrent();
            $table->timestamp('FCLK_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCLK_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FCLK_LinkedFacilityID')->references('FC_FacilityID')->on('Facilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityLinks');
    }
}
