<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Equipments', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('EQ_EquipmentID');

            // Columns
            $table->string('EQ_Equipment', 50)->unique();
            $table->enum('EQ_Equipment_Type', ['ET', 'SW']);

            // Timestamps
            $table->integer('EQ_CreatedBy');
            $table->timestamp('EQ_CreatedDate');
            $table->integer('EQ_UpdatedBy');
            $table->timestamp('EQ_UpdatedDate');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Equipments');
    }
}
