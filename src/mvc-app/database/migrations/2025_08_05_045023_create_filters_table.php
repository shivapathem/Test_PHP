<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Filters', function (Blueprint $table) {
            // Primary Key
            $table->increments('FLR_FilterID');

            //Columns
            $table->unsignedInteger('FLR_CreatedBy');
            $table->unsignedInteger('FLR_UpdatedBy');
            $table->string('FLR_FilterType', 20);
            $table->string('FLR_FilterPrivacyType', 20);
            $table->string('FLR_FilterName', 50);
            $table->json('FLR_FilterData_JSON');

            //Created and Updated timestamps
            $table->timestamp('FLR_CreatedDate')->useCurrent();
            $table->timestamp('FLR_UpdatedDate')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Filters');
    }
}
