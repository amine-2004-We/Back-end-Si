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
        Schema::create('evaluation_grid_operations', function (Blueprint $table) {
            $table->id();
            $table->string('grid_code');
            $table->string('evaluation_code');
            $table->string('title');
            $table->foreignId('task_evaluation_id')
                ->constrained('task_evaluations')
                ->onDelete('restrict');
            $table->string('educational_area');
            $table->string('targeted_overall_skill');
            $table->string('sub_skill');
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->onDelete('restrict');
            $table->foreignId('program_id')
                ->nullable()
                ->constrained('programs')
                ->onDelete('restrict');
            $table->enum('niveau_appreciation', [
                '4',
                '3',
                '2',
                '1',
                '0',
                'Non tenté',
                'Absent'
            ]);
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('grid_version')->nullable();
            $table->enum('grid_status',[
                'Brouillon',
                'Validée',
                'Archivée'
            ]);
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_evaluation_grid_operations');
    }
};
