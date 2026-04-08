<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ExternalCustomers', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('EC_ExternalCustomerID');

            // Columns
            $table->string('EC_CompanyName', 50)->unique();
            $table->string('EC_BuildingNumber')->nullable();
            $table->string('EC_Street')->nullable();
            $table->string('EC_City')->nullable();
            $table->string('EC_County')->nullable();
            $table->string('EC_PostCode')->nullable();
            $table->string('EC_Country')->nullable();
            $table->string('EC_InternationalAddress')->nullable();
            $table->string('EC_ContactName', 50);
            $table->string('EC_Position', 50)->nullable();
            $table->string('EC_ContactNumber', 25);
            $table->string('EC_ContactEmail', 60);

            // Timestamps
            $table->integer('EC_CreatedBy');
            $table->timestamp('EC_CreatedDate');
            $table->integer('EC_UpdatedBy');
            $table->timestamp('EC_UpdatedDate');
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
        Schema::dropIfExists('ExternalCustomers');
    }
}
