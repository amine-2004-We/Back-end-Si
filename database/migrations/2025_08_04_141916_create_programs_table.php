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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('program_id')->unique()->nullable();
            $table->string('title');
            $table->string('code')->unique();
            
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            $table->text('main_objective');
            $table->string('type'); 
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('planned_activities_count')->nullable();
            $table->enum('status', ['Actif', 'Clôturé', 'En pause', 'Archivé'])->default('Actif');
            
            $table->foreignId('operational_manager_id')->nullable()->constrained('collaborators')->onDelete('set null');
            $table->foreignId('pedagogical_manager_id')->nullable()->constrained('collaborators')->onDelete('set null');
            $table->foreignId('regional_manager_id')->nullable()->constrained('collaborators')->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('collaborators')->onDelete('set null');
            
            $table->text('observations')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};