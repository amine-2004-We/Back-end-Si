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
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->foreignId('department_id')->constrained('departements');
            $table->foreignId('position_id')->constrained('position');
            $table->unsignedInteger('number_of_positions');
            $table->enum('recruitment_reason', ['Remplacement', "Création de poste"]);
            $table->text('required_skills')->nullable();
            $table->date('desired_start_date')->nullable();
            $table->enum('status', ['En attente', 'Validée', 'Rejeté'])->default('En attente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_requests');
    }
};
