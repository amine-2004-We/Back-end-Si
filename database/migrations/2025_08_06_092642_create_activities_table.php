<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->unique()->nullable();
            $table->string('title');
            
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('responsible_collaborator_id')->nullable()->constrained('collaborators')->onDelete('set null');
            
            $table->string('type'); // This is the discriminator column
            
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->integer('duration_minutes')->unsigned();
            
            $table->foreignId('location_site_id')->nullable()->constrained('sites')->onDelete('set null');

            $table->enum('status', ['Prévue', 'Réalisée', 'Annulée', 'Reportée'])->default('Prévue');
            $table->text('field_observations')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
