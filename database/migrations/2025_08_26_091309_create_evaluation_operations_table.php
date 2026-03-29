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
        Schema::create('evaluation_operations', function (Blueprint $table) {
            $table->id();
            $table->string('evaluation_code');
            $table->foreignId('beneficiary_id')->constrained('beneficiaires')->onDelete('restrict');
            $table->foreignId('session_id')->constrained('task_pedagogical_sessions')->onDelete('restrict');
            $table->foreignId('evaluator')->constrained('collaborators')->onDelete('restrict');
            $table->string('comment')->nullable();
            $table->enum('evaluation_status_operation', ['Réalisée', 'Non Réalisée','Absent','en cours'])->default('en cours');
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_operations');
    }
};
