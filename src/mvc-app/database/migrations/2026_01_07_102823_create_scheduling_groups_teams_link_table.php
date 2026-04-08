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
        Schema::create('scheduling_groups_teams_link', function (Blueprint $table) {
            $table->increments('SchedulingGroupsTeamsLinkID');    
            $table->integer('SchedulingGroupsID');
            $table->integer('SchedulingTeamID');
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->unsignedInteger('CreatedBy');
            $table->timestamp('CreatedDate')->useCurrent();
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->timestamp('UpdatedDate')->nullable()->useCurrent()->useCurrentOnUpdate();
            // Foreign Key Constraints
            $table->foreign('SchedulingGroupsID')->references('SchedulingGroupsID')->on('scheduling_groups')->onDelete('cascade');
            $table->foreign('SchedulingTeamID')->references('schedulingTeamId')->on('SchedulingTeams')->onDelete('cascade');
            $table->foreign('CreatedBy')->references('UD_UserID')->on('UserDetails')->onDelete('no action');
            $table->foreign('UpdatedBy')->references('UD_UserID')->on('UserDetails')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_groups_teams_link');
    }
};
