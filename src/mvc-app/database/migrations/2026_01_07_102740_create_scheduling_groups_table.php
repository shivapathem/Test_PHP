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
        Schema::create('scheduling_groups', function (Blueprint $table) {
            $table->increments('SchedulingGroupsID');
            $table->string('SchedulingGroupsName', 200);
            $table->unsignedInteger('DivisionID');
            $table->boolean('IsIncludeINMenu')->default(false);
            $table->text('Notes')->nullable();
            $table->unsignedInteger('CreatedBy');
            $table->timestamp('CreatedDate')->useCurrent();
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->timestamp('UpdatedDate')->nullable()->useCurrent()->useCurrentOnUpdate();
            // Soft delete column with custom name
            $table->softDeletes('DeletedAt');

            // Foreign Key Constraints
            $table->foreign('DivisionID')->references('DivisionID')->on('Divisions')->onDelete('cascade')->nullable();
            $table->foreign('CreatedBy')->references('UD_UserID')->on('UserDetails')->onDelete('cascade');
            $table->foreign('UpdatedBy')->references('UD_UserID')->on('UserDetails')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_groups');
    }
};
