<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilityEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityEquipments', function (Blueprint $table) {
            // Primary Key
            $table->increments('FCEQ_FacilityEquipmentID');
            $table->unsignedInteger('FCEQ_FacilityID');
            $table->unsignedInteger('FCEQ_EquipmentID');
            $table->unsignedInteger('FCEQ_Quantity')->nullable();
            $table->string('FCEQ_Note', 255)->nullable();

            //Created and Updated timestamps
            $table->timestamp('FCEQ_CreatedDate')->useCurrent();
            $table->timestamp('FCEQ_UpdatedDate')->useCurrent();

            //Foreign key
            $table->foreign('FCEQ_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FCEQ_EquipmentID')->references('EQ_EquipmentID')->on('Equipments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('FacilityEquipments');
    }
}
