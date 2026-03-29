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
        // Update modules table
        DB::table('modules')->update([
            'formation_type' => DB::raw("
                CASE 
                    WHEN formation_type = 'theoretical' THEN 'in_person'
                    WHEN formation_type = 'practical' THEN 'remote'
                    ELSE formation_type
                END
            ")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: convert back to old values
        DB::table('modules')->update([
            'formation_type' => DB::raw("
                CASE 
                    WHEN formation_type = 'in_person' THEN 'theoretical'
                    WHEN formation_type = 'remote' THEN 'practical'
                    ELSE formation_type
                END
            ")
        ]);
    }
};
