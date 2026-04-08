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
          Schema::table('FacilityMarkUnavailable', function (Blueprint $table) 
          {
            foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $table->enum('FMU_FacilityMarkUnavailableIsChecked_' . $day,[0, 1])->default(0);
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('FacilityMarkUnavailable', function (Blueprint $table) 
        {
            foreach (['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                $table->dropColumn('FMU_FacilityMarkUnavailableIsChecked_' . $day);
            }
        });
    }
};
