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
        Schema::create('external_trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('trainers')->onDelete('cascade');
            $table->string('trainer_identifier')->unique();
            $table->string('full_name');
            $table->enum('affiliation_type', ['Indépendant', 'Via cabinet']);
            $table->foreignId('cabinet_id')->nullable()->constrained('cabinets')->onDelete('set null');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('cv_path');
            $table->boolean('statut_actif')->default(true);
            $table->timestamp('date_enregistrement')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_trainers');
    }
};
