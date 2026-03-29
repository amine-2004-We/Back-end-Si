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
        // Convert all enum columns to varchar to avoid PostgreSQL check constraints
        DB::statement('ALTER TABLE prospections ALTER COLUMN douar_access TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN main_language TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN association_activity_type TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN owner_type TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN owner_status TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN manager_type TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN manager_structure TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN decision TYPE varchar(255)');
        
        // Convert all *_condition columns
        DB::statement('ALTER TABLE prospections ALTER COLUMN individual_tables_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN collective_tables_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN boards_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN display_boards_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN coat_hangers_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN teacher_desks_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN teacher_chairs_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN children_chairs_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN student_chairs_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN cabinets_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN storage_wardrobes_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN libraries_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN extinguishers_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN desktop_computers_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN laptops_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN printers_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN copiers_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN video_projectors_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN projection_screens_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN interactive_boards_condition TYPE varchar(255)');
        DB::statement('ALTER TABLE prospections ALTER COLUMN other_equipment_condition TYPE varchar(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert if needed
    }
};
