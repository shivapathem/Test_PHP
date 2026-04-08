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
        try {
            Schema::table('FacilityTypes', function (Blueprint $table) {
                $table->dropUnique(['FT_FacilityType']);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
        try {
            Schema::table('Locations', function (Blueprint $table) {
                // Drop the unique constraint 
                $table->dropUnique(['LN_Location']);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
        try {
            Schema::table('Services', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique(['SR_Service']);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
        try {
            Schema::table('Equipment', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique(['EQ_Equipment']);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
        try {
            Schema::table('Equipment', function (Blueprint $table) {
                $table->dropUnique('equipments_eq_equipment_unique');
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
        try {
            Schema::table('Facilities', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique(['FC_FacilityName']);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist or different name—ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('FacilityTypes', function (Blueprint $table) {
                // Add the unique constraint 
                $table->unique('FT_FacilityType');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('Locations', function (Blueprint $table) {
                // Add the unique constraint 
                $table->unique('LN_Location');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('Services', function (Blueprint $table) {
                // Add the unique constraint
                $table->unique('SR_Service');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('Equipment', function (Blueprint $table) {
                // Add the unique constraint
                $table->unique('EQ_Equipment');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('Facilities', function (Blueprint $table) {
                // Add the unique constraint
                $table->unique('FC_FacilityName');
            });
        } catch (\Throwable $e) {
        }
    }
};
