<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_collaborator_training', function (Blueprint $table) {
            $table->foreignId('trainee_collaborator_id')
                ->constrained('trainee_collaborators')
                ->cascadeOnDelete();

            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();

            $table->enum('training_evaluation_status', ['success', 'average', 'failure'])->nullable();
            $table->boolean('satisfaction_evaluation')->nullable();
            $table->date('registered_at')->nullable();

            $table->unique(['trainee_collaborator_id', 'training_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainee_collaborator_training');
    }
};
