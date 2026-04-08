<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Actions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Actions', function (Blueprint $table) {
            $table->id('action_id'); // Auto-increment numeric PK
            $table->string('action_name'); // Required text field
            $table->text('description');   // Required text field
            // Adds created_at & updated_at columns.
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Actions');
    }
}
