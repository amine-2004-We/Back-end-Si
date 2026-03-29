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
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropForeign(['birth_region_id']);
            $table->dropForeign(['residence_region_id']);
            $table->dropForeign(['assigned_region_id']);
        });
        Schema::table('collaborators', function (Blueprint $table) {
            $table->foreign('birth_region_id')->references('id')->on('regions')->restrictOnDelete();
            $table->foreign('residence_region_id')->references('id')->on('regions')->restrictOnDelete();
            $table->foreign('assigned_region_id')->references('id')->on('regions')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            // Drop the foreign keys referencing regions
            $table->dropForeign(['birth_region_id']);
            $table->dropForeign(['residence_region_id']);
            $table->dropForeign(['assigned_region_id']);

            // Re-add foreign keys referencing provinces
            $table->foreign('birth_region_id')->references('id')->on('provinces')->restrictOnDelete();
            $table->foreign('residence_region_id')->references('id')->on('provinces')->restrictOnDelete();
            $table->foreign('assigned_region_id')->references('id')->on('provinces')->restrictOnDelete();
        });
    }
};
