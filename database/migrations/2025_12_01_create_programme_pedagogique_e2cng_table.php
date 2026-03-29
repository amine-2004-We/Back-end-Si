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
        Schema::create('programme_pedagogique_e2cng', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained('groups')->restrictOnDelete();
            $table->string('project_pedagogique')->nullable();
            $table->string('project_pedagogique_arabe')->nullable();
            $table->string('metier')->nullable();
            $table->string('metier_arabe')->nullable();
            $table->json('ateliers')->nullable();
            $table->json('ateliers_arabe')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_pedagogique_e2cng');
    }
};
