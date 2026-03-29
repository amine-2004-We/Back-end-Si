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
        Schema::dropIfExists('tasks');

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique()->nullable();
            $table->string('title');

            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('phase_id')->constrained('phases')->onDelete('cascade');
            $table->foreignId('responsible_collaborator_id')->nullable()->constrained('collaborators')->onDelete('set null');
            $table->foreignId('location_site_id')->nullable()->constrained('sites')->onDelete('set null');
            $table->foreignId('budget_line_id')->nullable()->constrained('budget_lines')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('type'); 
            
            $table->date('expected_start_date');
            $table->date('expected_end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->integer('duration_minutes')->unsigned()->nullable();

            $table->enum('status', ['Prévue', 'En cours', 'Réalisée', 'Annulée', 'Reportée'])->default('Prévue');
            $table->text('field_observations')->nullable();
            $table->string('associated_document')->nullable();
            
            $table->unique(['title', 'project_id']);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
