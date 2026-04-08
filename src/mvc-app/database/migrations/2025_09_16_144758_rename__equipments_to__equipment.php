<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEquipmentsToEquipment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('FacilityEquipments', function (Blueprint $table) {
            $table->dropForeign(['FCEQ_EquipmentID']);
        });

        Schema::rename('Equipments', 'Equipment');

        Schema::table('FacilityEquipments', function (Blueprint $table) {
            $table->foreign('FCEQ_EquipmentID')->references('EQ_EquipmentID')->on('Equipment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FacilityEquipments', function (Blueprint $table) {
            $table->dropForeign(['FCEQ_EquipmentID']);
        });

        Schema::rename('Equipment', 'Equipments');

        Schema::table('FacilityEquipments', function (Blueprint $table) {
            $table->foreign('FCEQ_EquipmentID')->references('EQ_EquipmentID')->on('Equipments');
        });
    }
}
