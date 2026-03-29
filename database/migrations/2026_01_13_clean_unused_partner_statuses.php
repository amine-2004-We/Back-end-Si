<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $statusToKeep = ['Prospect', 'En cours', 'Partenaire actif', 'Clôturé'];
        
        // ÉTAPE 1 : Créer/Vérifier que les 4 statuts valides existent
        foreach ($statusToKeep as $statusName) {
            DB::table('status_partners')->updateOrInsert(
                ['name' => $statusName],
                ['name' => $statusName, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        
        // ÉTAPE 2 : Récupérer le statut "Partenaire actif" (statut par défaut pour réassignation)
        $activePartnerStatus = DB::table('status_partners')
            ->where('name', 'Partenaire actif')
            ->first();
        
        if (!$activePartnerStatus) {
            throw new \Exception("Le statut 'Partenaire actif' n'a pas pu être créé !");
        }
        
        // ÉTAPE 3 : Identifier les statuts à supprimer
        $unusedStatuses = DB::table('status_partners')
            ->whereNotIn('name', $statusToKeep)
            ->get();
        
        if ($unusedStatuses->count() > 0) {
            // ÉTAPE 4 : Réassigner TOUS les partenaires ayant un statut à supprimer
            DB::table('partners')
                ->whereIn('status_id', $unusedStatuses->pluck('id')->toArray())
                ->update(['status_id' => $activePartnerStatus->id]);
            
            // ÉTAPE 5 : PUIS supprimer les statuts inutilisés
            DB::table('status_partners')
                ->whereNotIn('name', $statusToKeep)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vous pouvez implémenter le rollback si nécessaire
        // Pour l'instant, laissez vide car vous ne voulez pas restaurer les statuts supprimés
    }
};
