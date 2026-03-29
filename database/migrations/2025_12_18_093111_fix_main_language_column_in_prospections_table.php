<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            // Change main_language from enum to varchar to avoid PostgreSQL check constraint issues
            DB::statement('ALTER TABLE prospections ALTER COLUMN main_language TYPE varchar(255)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospections', function (Blueprint $table) {
            // Change back to enum type
            DB::statement("ALTER TABLE prospections ALTER COLUMN main_language TYPE varchar(255)");
        });
    }
};
