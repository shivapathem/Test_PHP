<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueCompanyName extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('ExternalCustomers', function (Blueprint $table) {
            // Drop the unique constraint on EC_CompanyName
            $table->dropUnique(['EC_CompanyName']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ExternalCustomers', function (Blueprint $table) {
            $table->unique('EC_CompanyName');
        });
    }
}
