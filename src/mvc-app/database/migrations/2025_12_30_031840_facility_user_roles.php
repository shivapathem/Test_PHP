<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('FacilityUserRoles', function (Blueprint $table) {
            // Primary key, auto-incrementing integer
            $table->increments('FUR_FacilityUserRoleID');

            // Columns
            $table->unsignedInteger('FUR_FacilityID');
            $table->unsignedInteger('FUR_UserID');
            $table->string('FUR_Role');

            // Timestamps
            $table->integer('FUR_CreatedBy');
            $table->timestamp('FUR_CreatedDate');
            $table->integer('FUR_UpdatedBy');
            $table->timestamp('FUR_UpdatedDate');
            $table->softDeletes();

            //Foreign key
            $table->foreign('FUR_FacilityID')->references('FC_FacilityID')->on('Facilities');
            $table->foreign('FUR_UserID')->references('UD_UserID')->on('UserDetails');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('FacilityUserRoles');
    }
};
