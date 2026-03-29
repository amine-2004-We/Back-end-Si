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
        // Drop the assigned_project column from collaborators table
        Schema::table('collaborators', function (Blueprint $table) {
            $table->dropColumn('assigned_project');
        });

        // Create the pivot table between projects and collaborators
        Schema::create('project_collaborator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained()->onDelete('restrict');
            $table->foreignId('project_id')->constrained('projects')->onDelete('restrict');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the pivot table
        Schema::dropIfExists('project_collaborator');

        // Recreate the assigned_project foreign key column in collaborators
        Schema::table('collaborators', function (Blueprint $table) {
            $table->foreignId('assigned_project')
                ->nullable()
                ->constrained('projects')
                ->restrictOnDelete();
        });
    }
};
