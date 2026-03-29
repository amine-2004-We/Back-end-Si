<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_evaluations', function (Blueprint $table) {
            $table->id();

            $table->string('evaluation_id')->unique();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('participant_id')
                ->constrained('participants')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('trainer_id')
                ->constrained('trainers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('evaluated_at');

            $table->string('evaluation_type');

            $table->foreignId('competency_grid_id')
                ->nullable()
                ->constrained('competency_grids')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('score_value', 8, 2)->nullable();
            $table->string('score_label')->nullable();

            $table->text('trainer_comments')->nullable();
            $table->json('attachments')->nullable();

            $table->string('status')->default('planned');
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['module_id', 'participant_id', 'deleted_at'],
                'eval_unique_module_participant_active'
            );

            $table->index(['trainer_id', 'evaluated_at'], 'eval_trainer_date_idx');
            $table->index('status', 'eval_status_idx');
            $table->index('evaluation_type', 'eval_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_evaluations');
    }
};
