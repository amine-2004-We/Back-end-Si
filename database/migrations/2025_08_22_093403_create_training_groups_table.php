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
        Schema::create('training_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('target_size')->nullable();
            $table->integer('current_size');
            $table->text('remarks')->nullable();
            $table->enum('status', ['active','closed','cancelled'])->default('active');
            $table->foreignId('created_by_id')->nullable()
                ->constrained('collaborators')
                ->nullOnDelete();
            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_groups');
    }
};
