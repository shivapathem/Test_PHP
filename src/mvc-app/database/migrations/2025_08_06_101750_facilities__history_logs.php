<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FacilitiesHistoryLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('HistoryLogs', function (Blueprint $table) {
            // Primary Key
            $table->increments('HL_ID');
            // History
            $table->string('HL_Type', 50);
            $table->unsignedInteger('HL_AttributeID');
            $table->json('HL_HLogs');
            $table->integer('HL_Created_BY')->nullable();
            $table->dateTime('HL_Created_ON')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('HistoryLogs');
    }
}
