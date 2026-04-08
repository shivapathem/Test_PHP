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
        Schema::table('Facilities', function (Blueprint $table) {
            $table->string('FC_LocationNote', 255)
                ->nullable()
                ->change();

            $table->string('FC_FacilityNote', 255)
                ->nullable()
                ->change();

            $table->string('FC_PopupNote', 255)
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Facilities', function (Blueprint $table) {
            $table->string('FC_LocationNote', 255)
                ->nullable(false)
                ->change();

            $table->string('FC_FacilityNote', 255)
                ->nullable(false)
                ->change();

            $table->string('FC_PopupNote', 255)
                ->nullable(false)
                ->change();
        });
    }
};
