<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddECPostCodeToExternalCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ExternalCustomers', function (Blueprint $table) {
            // UK postcodes max length is 8-9, but 10 allows extra flexibility
            if (!Schema::hasColumn('ExternalCustomers', 'EC_PostCode')) {
                $table->string('EC_PostCode', 10)->nullable()->after('EC_County');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ExternalCustomers', function (Blueprint $table) {
            $table->dropColumn('EC_PostCode');
        });
    }
}
