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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('evaluation_code');
            $table->foreignId('object_project')->nullable()->constrained('projects')->restrictOnDelete();
            $table->foreignId('object_partner')->nullable()->constrained('partners')->restrictOnDelete();
            $table->enum('evaluation_type',['Évaluation finale', 'Évaluation intermédiaire','Suivi qualité','Audit']);
            $table->date('evaluation_date');
            $table->date('evaluation_period_start_date');
            $table->date('evaluation_period_end_date');
            $table->foreignId('evaluator_id')->constrained('collaborators')->restrictOnDelete();
            $table->double('total_score');
            $table->string('comment')->nullable();
            $table->enum('evaluation_status',['En préparation', 'En cours','Validée','Archivée']);
            $table->foreignId('notification')->nullable()->constrained('collaborators')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation');
    }
};
