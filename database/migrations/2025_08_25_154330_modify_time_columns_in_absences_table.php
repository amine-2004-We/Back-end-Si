<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import the DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE absences ALTER COLUMN start_time TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE absences ALTER COLUMN end_time TYPE VARCHAR(255)");

        DB::table('absences')->update(['start_time' => 'AM', 'end_time' => 'PM']);

        DB::statement("ALTER TABLE absences ADD CONSTRAINT absences_start_time_check CHECK (start_time IN ('AM', 'PM'))");
        DB::statement("ALTER TABLE absences ADD CONSTRAINT absences_end_time_check CHECK (end_time IN ('AM', 'PM'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropCheck('absences_start_time_check');
            $table->dropCheck('absences_end_time_check');
        });

        DB::statement("ALTER TABLE absences ALTER COLUMN start_time TYPE TIME USING '00:00:00'::TIME");
        DB::statement("ALTER TABLE absences ALTER COLUMN end_time TYPE TIME USING '00:00:00'::TIME");
    }
};
