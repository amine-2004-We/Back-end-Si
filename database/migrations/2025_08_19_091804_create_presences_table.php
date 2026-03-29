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
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->string('presence_id')->unique();

            $table->morphs('personable');

            $table->foreignId('task_id')->constrained('tasks')->comment('Activité / Événement associé');
            
            $table->date('event_date');
            
            $table->enum('status', ['Présent', 'Absent', 'En retard', 'Excusé']);
            
            $table->time('arrival_time')->nullable();
            $table->string('justification')->nullable();
            $table->text('observations')->nullable();

            $table->foreignId('declared_by')->constrained('users')->comment('Personne qui a saisi la présence');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};