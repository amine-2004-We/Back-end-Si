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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
             $table->foreignId('job_posting_id')->constrained('job_postings');
            $table->unsignedTinyInteger('number_of_children')->nullable();
            $table->string('title'); // civilite
            $table->string('last_name'); // nom
            $table->string('first_name'); // prenom
            $table->string('last_name_ar')->nullable(); // nomArabe
            $table->string('first_name_ar')->nullable(); // prenomArabe
            $table->string('phone'); // telephone
            $table->string('email')->unique();
            $table->string('cin')->unique();
            $table->string('cnss')->unique()->nullable();
            $table->string('rib');
            $table->date('birth_date'); // date_naissance
            $table->text('residence_address'); // adresse_residence
            $table->foreignId('birth_region_id')->constrained('regions');
            $table->foreignId('birth_province_id')->constrained('provinces');
            $table->foreignId('residence_region_id')->constrained('regions');
            $table->foreignId('residence_province_id')->constrained('provinces');
            $table->string('source');
            $table->string('status')->default('Nouveau');
            $table->integer('total_experience')->nullable(); // experience_totale
            $table->integer('educational_experience')->nullable(); // experience_education
            $table->string('marital_status'); // situation_familiale
            $table->string('education_level')->nullable(); // formation
            $table->string('discipline')->nullable();
            $table->string('institution')->nullable(); // etablissement
            $table->date('graduation_date')->nullable(); // date_obtention
            $table->string('photo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
