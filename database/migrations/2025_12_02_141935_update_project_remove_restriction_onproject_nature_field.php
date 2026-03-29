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
        DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS projects_project_nature_check');

        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_nature')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change back to enum
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('project_nature', ['Opérationnel', 'Institutionnel', 'Expérimental'])->change();
        });
    }
};
