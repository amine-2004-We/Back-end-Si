<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->string('beneficiary_id')->unique()->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->enum('gender', ['Masculin', 'Féminin']);
            $table->date('date_of_birth');
            $table->string('place_of_residence');
            $table->string('massar_code')->nullable();
            $table->enum('nationality', ['Marocain', 'Étranger'])->default('Marocain');
            $table->text('address');

            $table->foreignId('current_school_level_id')
                ->nullable()
                ->constrained('levels')
                ->onDelete('set null');
            $table->unsignedBigInteger('group_id')->nullable();

            $table->enum('status', ['Radié', 'Archivé', 'Actif', 'En pause'])->default('Actif');
            $table->date('enrollment_date');
            $table->date('radiation_date')->nullable();
            $table->enum('radiation_reason', ['Abandon', 'Mutation', 'Désistement', 'Autre'])->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaires');
    }
};
