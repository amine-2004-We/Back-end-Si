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
        $tablesToDrop = [
            'task_group', 'activity_group',
            'task_attachments', 'activity_attachments',
            'task_evaluations', 'activity_evaluations',
            'task_meetings', 'activity_meetings',
            'task_pedagogical_sessions', 'activity_pedagogical_sessions',
            'task_visits', 'activity_visits',
            'task_ateliers', 'activity_ateliers',
        ];

        foreach ($tablesToDrop as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        Schema::create('task_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('evaluated_beneficiary_id')->constrained('beneficiaires')->onDelete('cascade');
            $table->foreignId('linked_evaluation_session_id')->nullable()->constrained('tasks')->onDelete('set null');
            $table->string('evaluation_grid_code')->nullable();
            $table->string('learning_domain');
            $table->string('targeted_competency');
            $table->string('achieved_level');
            $table->text('qualitative_comment')->nullable();
            $table->enum('participation_status', ['Réalisée', 'Absent', 'Non Participatif']);
            $table->timestamps();
        });

        Schema::create('task_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('theme');
            $table->integer('expected_participants_count')->unsigned();
            $table->text('objectives');
            $table->text('distributed_documents')->nullable();
            $table->timestamps();
        });

        Schema::create('task_pedagogical_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->integer('expected_beneficiaries_count')->unsigned();
            $table->timestamps();
        });

        Schema::create('task_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('subject');
            $table->foreignId('observed_collaborator_id')->nullable()->constrained('collaborators')->onDelete('set null');
            $table->text('objectives');
            $table->string('observation_grid_info')->nullable();
            $table->timestamps();
        });

        Schema::create('task_ateliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('theme');
            $table->text('objectives');
            $table->integer('expected_participants_count')->unsigned();
            $table->timestamps();
        });

        Schema::create('task_group', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->primary(['task_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_group');
        Schema::dropIfExists('task_ateliers');
        Schema::dropIfExists('task_visits');
        Schema::dropIfExists('task_pedagogical_sessions');
        Schema::dropIfExists('task_meetings');
        Schema::dropIfExists('task_evaluations');
        Schema::dropIfExists('task_attachments');
    }
};
