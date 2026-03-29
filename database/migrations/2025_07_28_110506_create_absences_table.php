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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->text('reason');
            $table->foreignId('collaborator_id')->constrained('collaborators');
            $table->enum('absence_type', ['Maladie', 'Éducatif', 'Administratif', 'événements familiaux', 'mesures disciplinaire']);
            $table->enum('absence_status', ['Justifié', 'Non justifié', 'autorisé']);
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
