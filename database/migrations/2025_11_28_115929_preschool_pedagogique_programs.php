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
        Schema::create('preschool_pedagogique_programs', function (Blueprint $table) {
            $table->id();
            $table->string('pedagogical_project')->nullable();
            $table->string('pedagogical_project_arabe')->nullable();
            $table->foreignId('subcomponent')->constrained('phases')->restrictOnDelete();
            $table->integer('duration');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preschool_pedagogique_programs');
    }
};
