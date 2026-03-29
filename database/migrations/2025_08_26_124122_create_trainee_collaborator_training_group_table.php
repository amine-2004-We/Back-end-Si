<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_group_trainee_collaborator', function (Blueprint $table) {
            $table->foreignId('trainee_collaborator_id')
                ->constrained('collaborators')
                ->cascadeOnDelete();

            $table->foreignId('training_group_id')
                ->constrained('training_groups')
                ->cascadeOnDelete();

            $table->unique(['trainee_collaborator_id', 'training_group_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_group_trainee_collaborator');
    }
};
