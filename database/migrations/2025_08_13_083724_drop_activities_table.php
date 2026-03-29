<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'activity_attachments',
            'activity_evaluations',
            'activity_meetings',
            'activity_pedagogical_sessions',
            'activity_visits',
            'activity_ateliers',
        ];

        foreach ($tables as $oldTableName) {
            if (Schema::hasTable($oldTableName)) {
                $newTableName = str_replace('activity_', 'task_', $oldTableName);

                DB::table($oldTableName)->truncate();
                Schema::rename($oldTableName, $newTableName);

                Schema::table($newTableName, function (Blueprint $table) use ($oldTableName, $newTableName) {
                    $foreignKeyName = $oldTableName . '_activity_id_foreign';
                    $table->dropForeign($foreignKeyName);
                    
                    $table->renameColumn('activity_id', 'task_id');
                    $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');

                    // FIX: Specifically handle the extra foreign key in the evaluations table.
                    if ($newTableName === 'task_evaluations') {
                        $table->dropForeign('activity_evaluations_linked_evaluation_session_id_foreign');
                        $table->foreign('linked_evaluation_session_id')->references('id')->on('tasks')->onDelete('set null');
                    }
                });
            }
        }
        
        if (Schema::hasTable('activity_group')) {
            DB::table('activity_group')->truncate();
            
            Schema::rename('activity_group', 'task_group');
            Schema::table('task_group', function (Blueprint $table) {
                $table->dropForeign('activity_group_activity_id_foreign');
                $table->renameColumn('activity_id', 'task_id');
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('activities')) {
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->string('activity_id')->unique()->nullable();
                $table->string('title');
                $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
                $table->foreignId('responsible_collaborator_id')->nullable()->constrained('collaborators')->onDelete('set null');
                $table->string('type');
                $table->date('planned_date');
                $table->date('actual_date')->nullable();
                $table->integer('duration_minutes')->unsigned();
                $table->foreignId('location_site_id')->nullable()->constrained('sites')->onDelete('set null');
                $table->enum('status', ['Prévue', 'Réalisée', 'Annulée', 'Reportée'])->default('Prévue');
                $table->text('field_observations')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }
};
