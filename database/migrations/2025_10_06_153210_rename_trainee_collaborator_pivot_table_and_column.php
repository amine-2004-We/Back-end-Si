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
        // First, rename the table to follow Laravel's convention (alphabetical order)
        Schema::rename('training_group_trainee_collaborator', 'collaborator_training_group');

        // Next, rename the column within the newly named table
        Schema::table('collaborator_training_group', function (Blueprint $table) {
            // Laravel is smart enough to rename the foreign key constraints and indexes as well
            $table->renameColumn('trainee_collaborator_id', 'collaborator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaborator_training_group', function (Blueprint $table) {
            // Reverse the column rename
            $table->renameColumn('collaborator_id', 'trainee_collaborator_id');
        });

        // Reverse the table rename
        Schema::rename('collaborator_training_group', 'training_group_trainee_collaborator');
    }
};
