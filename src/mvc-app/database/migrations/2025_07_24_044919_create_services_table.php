<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Services', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('SR_ServiceID');

            // Columns
            $table->string('SR_Service', 50)->unique();

            // Timestamps
            $table->integer('SR_CreatedBy');
            $table->timestamp('SR_CreatedDate');
            $table->integer('SR_UpdatedBy');
            $table->timestamp('SR_UpdatedDate');
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
        Schema::dropIfExists('Services');
    }
}
