<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_TeamID2');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_TeamID');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_ScheduledPersonID');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_ScheduledType');

        Schema::table('ScheduledPersonTeam_LINK', function (Blueprint $table) {
            $table->unsignedInteger('DisplayInViewScreen')->default(1);
            $table->unsignedInteger('IsHomeTeam')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_TeamID2');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_TeamID');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_ScheduledPersonID');
        $this->dropIndexIfExists('ScheduledPersonTeam_LINK', 'idx_ScheduledPersonTeam_LINK_ScheduledType');
        Schema::table('ScheduledPersonTeam_LINK', function (Blueprint $table) {
            $table->dropColumn('DisplayInViewScreen');
            $table->boolean('IsHomeTeam')->change();
        });
    }

    function dropIndexIfExists(string $table, string $index)
    {
        DB::statement("
        IF EXISTS (
            SELECT 1 FROM sys.indexes
            WHERE name = '$index'
              AND object_id = OBJECT_ID('$table')
        )
        DROP INDEX [$index] ON [$table];
    ");
    }
};
