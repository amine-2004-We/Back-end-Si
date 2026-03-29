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
        Schema::table('phases', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['responsible_id']);
            $table->dropColumn([
                'project_id',
                'type',
                'execution_order',
                'planned_start_date',
                'planned_end_date',
                'actual_start_date',
                'actual_end_date',
                'responsible_id',
                'comments',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade')->after('phase_identifier');
            $table->enum('type', ['Préparation', 'Mise en œuvre', 'Suivi-évaluation', 'Clôture'])->after('name');
            $table->date('planned_start_date')->after('type');
            $table->date('planned_end_date')->after('planned_start_date');
            $table->date('actual_start_date')->nullable()->after('planned_end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->after('execution_order');
            $table->text('comments')->nullable()->after('responsible_id');
        });
    }
};
