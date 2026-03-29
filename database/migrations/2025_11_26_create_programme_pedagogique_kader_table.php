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
        Schema::create('programme_pedagogique_kader', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class')->restrictOnDelete();
            $table->string('project_pedagogique')->nullable();
            $table->string('project_pedagogique_arabe')->nullable();
            $table->json('sections')->nullable();
            $table->string('sections_arabe')->nullable();
            $table->json('activities')->nullable(); 
            $table->string('activities_arabe')->nullable();
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
        Schema::dropIfExists('programme_pedagogique_kader');
    }
};
