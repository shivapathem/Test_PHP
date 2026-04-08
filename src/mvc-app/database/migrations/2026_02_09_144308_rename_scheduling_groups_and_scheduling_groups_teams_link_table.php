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
        Schema::rename('scheduling_groups', 'SchedulingGroups');
        Schema::rename('scheduling_groups_teams_link', 'SchedulingGroupsTeamsLinks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('SchedulingGroups', 'scheduling_groups');
        Schema::rename('SchedulingGroupsTeamsLinks', 'scheduling_groups_teams_link');
    }
};
