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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_identifier')->unique()->comment('Identifiant de la séance');
            $table->foreignId('module_id')->constrained('modules')->comment('Module concerné');
            $table->foreignId('training_id')->constrained('trainings')->comment('Formation associée (héritée)');
            $table->foreignId('training_group_id')->constrained('training_groups')->comment('Groupe formé concerné');

            $table->morphs('animator'); 

            $table->date('session_date')->comment('Date de la séance');
            $table->time('start_time')->nullable()->comment('Heure de début');
            $table->time('end_time')->nullable()->comment('Heure de fin');
            $table->integer('planned_duration_hours')->comment('Durée prévue (en heures)');
            
            $table->foreignId('site_id')->constrained('sites')->comment('Lieu (site ou à distance)');
            $table->enum('session_type', ['Theorique', 'Pratique', 'Evaluation'])->nullable()->comment('Type de séance');

            $table->boolean('presence_registered')->default(false)->comment('Présences enregistrées ?');
            $table->text('observations')->nullable()->comment('Observations / remarques');
            $table->enum('status', ['Planifiée', 'Réalisée', 'Reportée', 'Annulée'])->default('Planifiée')->comment('Statut de la séance');

            $table->foreignId('created_by_id')->constrained('users')->comment('Créé par');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_session_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_session_attachments');
        Schema::dropIfExists('training_sessions');
    }
};