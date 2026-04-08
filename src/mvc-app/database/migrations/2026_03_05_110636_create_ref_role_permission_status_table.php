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
        // Modify RoleDescription column to accept longer text
        Schema::table('REF_Roles', function (Blueprint $table) {
            $table->string('RoleDescription', 1000)->nullable()->change();
        });

        Schema::create('REF_RolePermissionStatus', function (Blueprint $table) {
            $table->id('ID');
            $table->integer('MainRoleID');
            $table->integer('AdditionalRoleID');
            $table->string('PermissionKey', 20);
            $table->string('PermissionDescription', 1000)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->integer('CreatedBy')->nullable();
            $table->datetime('CreatedDate')->default(DB::raw('GETDATE()'));
            $table->integer('UpdatedBy')->nullable();
            $table->datetime('UpdatedDate')->nullable();
            
            // Add unique constraint
            $table->unique(['MainRoleID', 'AdditionalRoleID']);
            
            // Add foreign keys
            $table->foreign('MainRoleID')->references('RoleID')->on('REF_Roles');
            $table->foreign('AdditionalRoleID')->references('RoleID')->on('REF_Roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('REF_RolePermissionStatus');
        
        // Revert column size back to 100
        Schema::table('REF_Roles', function (Blueprint $table) {
            $table->string('RoleDescription', 100)->nullable()->change();
        });
    }
};
