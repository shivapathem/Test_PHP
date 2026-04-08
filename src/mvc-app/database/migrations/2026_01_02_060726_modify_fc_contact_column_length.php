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
            $table->longText('FC_Contact')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Facilities', function (Blueprint $table) {
            $table->string('FC_Contact', 50)->change();
        });
    }
};
