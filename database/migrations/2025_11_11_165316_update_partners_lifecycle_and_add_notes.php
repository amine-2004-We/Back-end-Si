<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema; // Assurez-vous que ceci est importé

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifier la table 'partners'
        Schema::table('partners', function (Blueprint $table) {

            // AJOUTER (avec vérification)
            if (!Schema::hasColumn('partners', 'date_debut_partenariat')) {
                $table->dateTime('date_debut_partenariat')->nullable()->after('status_id');
            }

            // AJOUTER (avec vérification)
            // Je le place après 'is_active' pour être sûr, car 'note' n'existe peut-être plus
            if (!Schema::hasColumn('partners', 'closure_reason')) {
                $table->text('closure_reason')->nullable()->after('is_active');
            }

            // SUPPRIMER (avec vérification)
            if (Schema::hasColumn('partners', 'note')) {
                $table->dropColumn('note');
            }
        });

        // 2. Créer la nouvelle table (avec vérification)
        if (!Schema::hasTable('partner_notes')) {
            Schema::create('partner_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
                $table->text('note');
                $table->timestamps(); // created_at servira de "date de saisie"
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Supprimer la table des notes (avec vérification)
        Schema::dropIfExists('partner_notes');

        // 2. Annuler les changements sur 'partners' (avec vérification)
        Schema::table('partners', function (Blueprint $table) {

            // Remettre l'ancien champ 'note' (s'il n'existe pas)
            if (!Schema::hasColumn('partners', 'note')) {
                $table->string('note')->nullable();
            }

            // Supprimer les nouveaux champs (s'ils existent)
            if (Schema::hasColumn('partners', 'date_debut_partenariat')) {
                $table->dropColumn('date_debut_partenariat');
            }
            if (Schema::hasColumn('partners', 'closure_reason')) {
                $table->dropColumn('closure_reason');
            }
        });
    }
};
