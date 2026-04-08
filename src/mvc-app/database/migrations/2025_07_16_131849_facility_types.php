<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FacilityTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FacilityTypes', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FT_FacilityTypeID');

            // Columns
            $table->string('FT_FacilityType', 50)->unique();

            // Foreign key columns
            $table->integer('FT_CreatedBy');
            $table->timestamp('FT_CreatedDate');
            $table->integer('FT_UpdatedBy');
            $table->timestamp('FT_UpdatedDate');
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
        Schema::dropIfExists('FacilityTypes');
    }
}
