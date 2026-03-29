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
        Schema::create('class_status_histories', function (Blueprint $table) {
            $table->id();
            
            // Reference to the class
            $table->foreignId('class_id')
                ->constrained('class')
                ->cascadeOnDelete();

            // Type of change: 'state' (Création/Transfert/Relocalisation) or 'status' (Opérationnel/En arrêt/etc)
            $table->enum('change_type', ['state', 'status'])->nullable();

            // Previous and new values
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            // Date of the change
            $table->date('change_date')->nullable();

            // Reason for the change (especially for status changes)
            $table->text('reason')->nullable();

            // Related projects and classes (nullable, only populated when relevant)
            $table->foreignId('transfer_to_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            $table->foreignId('relocate_to_class_id')
                ->nullable()
                ->constrained('class')
                ->nullOnDelete();

            $table->foreignId('perpetuation_project_id')
                ->nullable()
                ->constrained('projects')
                ->nullOnDelete();

            // User who made the change
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_status_histories');
    }
};
