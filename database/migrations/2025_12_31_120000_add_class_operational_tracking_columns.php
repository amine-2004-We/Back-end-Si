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
        Schema::table('class', function (Blueprint $table) {
            // Code de la classe interne (Code de l'unité + Ordre)
            $table->string('internal_class_code')->nullable()->after('class_code');

            // État de la classe: Création, Transfert, Relocalisation
            $table->enum('class_state', ['Création', 'Transfert', 'Relocalisation'])
                ->default('Création')->after('class_code');

            // Projet de destination en cas de transfert (entre projets)
            $table->foreignId('transfer_to_project_id')
                ->nullable()
                ->after('class_state')
                ->constrained('projects')
                ->nullOnDelete();

            // Classe de destination en cas de relocalisation (même projet)
            $table->foreignId('relocate_to_class_id')
                ->nullable()
                ->after('transfer_to_project_id')
                ->constrained('class')
                ->nullOnDelete();

            // Date de relocalisation
            $table->date('relocation_date')->nullable()->after('relocate_to_class_id');

            // Statut de la classe: Opérationnel, En arrêt, Résilié, Clôturé, Pérennisé
            $table->enum('class_status_value', ['Opérationnel', 'En arrêt', 'Résilié', 'Clôturé', 'Pérennisé'])
                ->default('Opérationnel')->after('relocation_date');

            // Date de changement de statut
            $table->date('status_change_date')->nullable()->after('class_status_value');

            // Motif du changement de statut
            $table->text('status_change_reason')->nullable()->after('status_change_date');

            // Projet de pérennisation
            $table->foreignId('perpetuation_project_id')
                ->nullable()
                ->after('status_change_reason')
                ->constrained('projects')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['transfer_to_project_id']);
            $table->dropForeignKeyIfExists(['relocate_to_class_id']);
            $table->dropForeignKeyIfExists(['perpetuation_project_id']);

            $table->dropColumn([
                'internal_class_code',
                'class_state',
                'transfer_to_project_id',
                'relocate_to_class_id',
                'relocation_date',
                'class_status_value',
                'status_change_date',
                'status_change_reason',
                'perpetuation_project_id',
            ]);
        });
    }
};
