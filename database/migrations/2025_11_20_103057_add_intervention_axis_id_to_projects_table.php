<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // On ajoute la colonne qui manquait
            $table->foreignId('intervention_axis_id')
                  ->nullable() // Important si vous avez déjà des projets en base
                  ->after('project_type_id') // Pour placer la colonne proprement
                  ->constrained('intervention_axes'); // Crée le lien SQL (Foreign Key)
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
             $table->dropForeign(['intervention_axis_id']);
            $table->dropColumn('intervention_axis_id');
        });
    }
};
