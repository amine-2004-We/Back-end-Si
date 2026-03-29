<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We use raw SQL because PostgreSQL requires the 'USING' clause
        // to convert an integer column to a JSON/JSONB column.
        DB::statement('
            ALTER TABLE projects
            ALTER COLUMN exercice_comptable TYPE JSONB
            USING CASE
                WHEN exercice_comptable IS NULL THEN NULL
                ELSE jsonb_build_array(exercice_comptable::text)
            END
        ');
        Schema::table('projects', function (Blueprint $table) {
            $table->string('analytic_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting from JSONB back to Integer (takes the first element of the array)
        DB::statement('
            ALTER TABLE projects
            ALTER COLUMN exercice_comptable TYPE INTEGER
            USING (exercice_comptable->>0)::integer
        ');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('analytic_code');
        });
    }
};
