<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vérifier si la table existe AVANT de la créer
        if (!Schema::hasTable('project_notes')) {
            Schema::create('project_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users');
                $table->text('content');
                $table->timestamps();
            });
        }

        // 2. Vérifier si la colonne 'notes' existe encore dans 'projects' avant de la supprimer
        if (Schema::hasColumn('projects', 'notes')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_notes');

        // On remet la colonne si elle n'existe pas déjà
        if (!Schema::hasColumn('projects', 'notes')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->text('notes')->nullable();
            });
        }
    }
};
