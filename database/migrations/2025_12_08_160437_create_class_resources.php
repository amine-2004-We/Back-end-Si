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
        Schema::create('class_resources', function (Blueprint $table) {
            $table->id();
            $table->ForeignId('class_id')->constrained('class')->restrictOnDelete();
            $table->ForeignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('isStill')->default(true);
            $table->boolean('isFavorite')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
