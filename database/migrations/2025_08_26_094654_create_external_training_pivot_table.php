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
        Schema::create('external_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_id')->constrained('externals')->onDelete('cascade');
            $table->foreignId('training_id')->constrained('trainings')->onDelete('cascade');
            $table->enum('training_evaluation', ['Réussite', 'Echec', 'A revoir'])->nullable();
            $table->enum('satisfaction_evaluation', ['Oui', 'Non'])->nullable();
            $table->timestamps();
            $table->unique(['external_id', 'training_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_training');
    }
};
