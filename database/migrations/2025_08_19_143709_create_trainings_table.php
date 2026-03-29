<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('training_id')->unique();
            $table->string('title');
            $table->enum('training_type', ['initial', 'continuous', 'monthly']);
            $table->foreignId('responsible_id')
                ->constrained('collaborators')
                ->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])
                ->default('planned');
            $table->enum('target_audience', ['candidates', 'collaborators', 'externals']);
            $table->foreignId('cabinet_id')
                ->nullable()
                ->constrained('cabinets')
                ->nullOnDelete();
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
