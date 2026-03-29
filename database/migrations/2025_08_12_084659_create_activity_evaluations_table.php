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
        Schema::create('activity_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('evaluated_beneficiary_id')->constrained('beneficiaires')->onDelete('cascade');
            $table->foreignId('linked_evaluation_session_id')->nullable()->constrained('activities')->onDelete('set null');
            $table->string('evaluation_grid_code')->nullable();
            $table->string('learning_domain');
            $table->string('targeted_competency');
            $table->string('achieved_level');
            $table->text('qualitative_comment')->nullable();
            $table->enum('participation_status', ['Réalisée', 'Absent', 'Non Participatif']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_evaluations');
    }
};
