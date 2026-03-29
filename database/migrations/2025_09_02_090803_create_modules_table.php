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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_id')->unique();
            $table->string('title');
            $table->text('pedagogical_objectives');
            
            $table->foreignId('training_id')->constrained('trainings')->nullOnDelete();
            
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            
            $table->foreignId('competency_grid_id')->nullable()->constrained('competency_grids')->nullOnDelete();
            
            $table->decimal('total_duration', 8, 2);
            
            $table->string('formation_type')->nullable();
            
            $table->json('pedagogical_supports')->nullable();
            
            $table->boolean('evaluation_planned')->default(false);
            
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');

            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
