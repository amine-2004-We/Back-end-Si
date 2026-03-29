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
        Schema::create('evaluation_criteria_operations', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_id');
            $table->
                foreignId('grid_evaluation_id')
                ->constrained('evaluation_grid_operations')
                ->onDelete('restrict');
            $table->string('title');
            $table->string('criteria_code')->nullable();
            $table->enum('grade_level', ['GS', 'MS', 'CP', 'CE1', 'CE2', 'CM1', 'CM2',]);
            $table->enum('evaluation_type', [
                'Observation directe',
                'Réponse orale',
                'Pratique',
            ]);
            $table->integer('weighting')->nullable();
            $table->enum('niveau_appreciation', ['4', '3', '2', '1', '0', 'Non tenté', 'Absent']);
            $table->string('success_indicators');
            $table->string('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria_operations');
    }
};
