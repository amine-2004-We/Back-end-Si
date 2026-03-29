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
        Schema::create('teacher_evaluations', function (Blueprint $table) {
            $table->id();
            // Primary Foreign Keys
            $table->foreignId('teacher_id')->nullable()->constrained('collaborators')->onDelete('set null'); 
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('program_type_id')->constrained('program_types')->onDelete('cascade');
            
          
            $table->foreignId('unit_id')->constrained('units')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');

         
            $table->integer('general_appearance')->comment('Aspect général');
            $table->integer('cleanliness')->comment('Propreté');
            $table->integer('punctuality')->comment('Ponctualité');
            $table->integer('respect_session_schedule')->comment('Respect d’horaire de la séance');
            $table->integer('preparation')->comment('Préparation');
            $table->integer('relationship_with_beneficiaries')->comment('Relation avec les bénéficiaires');
            $table->integer('treatment_of_objectives')->comment('Traitement des objectifs');
            $table->integer('assessment')->comment('Evaluation');
            $table->integer('innovation')->comment('Innovation');
            $table->integer('pedagogical_approach')->comment('Approche pédagogique');
            $table->integer('participation')->comment('Participation');
            $table->integer('error_correction')->comment('Rectification des erreurs');
            $table->integer('comprehension')->comment('Compréhension');
            $table->integer('knowledge_ritualization')->comment('Ritualisation des connaissances');

            // --- Summary and Comment Fields ---
            $table->float('total_score')->comment('Total');
            $table->float('percentage')->comment('Pourcentage');
            $table->text('low_score_reason')->nullable()->comment('Raisons(si la notation est au dessous de 19,5)');
            $table->text('observation')->nullable()->comment('Observation');
            
           
            $table->string('status')->default('draft')->comment('Status: draft,submitted, validated_rp, validated_ro, final_validated_rr');
          

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_evaluations');
    }
};