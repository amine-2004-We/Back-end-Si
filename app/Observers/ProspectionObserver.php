<?php

namespace App\Observers;

use App\Models\Prospection;
use Illuminate\Support\Facades\Auth;

/**
 * Observer pour gérer les événements du modèle Prospection.
 * Permet d'automatiser le remplissage des champs d'audit.
 */
class ProspectionObserver
{
    /**
     * Avant création du modèle.
     * Injecte automatiquement l'utilisateur créateur et calcule les totaux.
     */
    public function creating(Prospection $prospection): void
    {
        // Si le champ created_by est vide et qu'un utilisateur est connecté
        if (empty($prospection->created_by) && Auth::check()) {
            $prospection->created_by = Auth::id();
        }
        
        // Calculer les totaux
        $this->calculateTotals($prospection);
    }

    /**
     * Avant mise à jour du modèle.
     * Ajoute l'utilisateur responsable de la modification et recalcule les totaux.
     */
    public function updating(Prospection $prospection): void
    {
        if (Auth::check()) {
            $prospection->updated_by = Auth::id();
        }
        
        // Recalculer les totaux
        $this->calculateTotals($prospection);
    }

    /**
     * Lorsqu'une prospection est supprimée (soft delete ou hard delete).
     */
    public function deleted(Prospection $prospection): void
    {
        
    }

    
    public function restored(Prospection $prospection): void
    {
   
    }

    
    public function forceDeleted(Prospection $prospection): void
    {
        
    }

    /**
     * Calcule automatiquement tous les totaux basés sur les champs individuels.
     */
    private function calculateTotals(Prospection $prospection): void
    {
        // Totaux par groupe d'âge (enfants)
        if (!is_null($prospection->children_0_5_b) && !is_null($prospection->children_0_5_g)) {
            $prospection->children_0_5_total = $prospection->children_0_5_b + $prospection->children_0_5_g;
        }
        
        if (!is_null($prospection->children_6_12_b) && !is_null($prospection->children_6_12_g)) {
            $prospection->children_6_12_total = $prospection->children_6_12_b + $prospection->children_6_12_g;
        }
        
        if (!is_null($prospection->children_13_18_b) && !is_null($prospection->children_13_18_g)) {
            $prospection->children_13_18_total = $prospection->children_13_18_b + $prospection->children_13_18_g;
        }
        
        // Total enfants
        $children_total = 0;
        if (!is_null($prospection->children_0_5_total)) {
            $children_total += $prospection->children_0_5_total;
        }
        if (!is_null($prospection->children_6_12_total)) {
            $children_total += $prospection->children_6_12_total;
        }
        if (!is_null($prospection->children_13_18_total)) {
            $children_total += $prospection->children_13_18_total;
        }
        if ($children_total > 0) {
            $prospection->children_total = $children_total;
        }
        
        // Total jeunes 18-35
        if (!is_null($prospection->youth_m) && !is_null($prospection->youth_f)) {
            $prospection->youth_total = $prospection->youth_m + $prospection->youth_f;
        }
        
        // Total adultes > 35
        if (!is_null($prospection->adults_m) && !is_null($prospection->adults_f)) {
            $prospection->adults_total = $prospection->adults_m + $prospection->adults_f;
        }
        
        // Population totale = enfants + jeunes + adultes
        $total_pop = 0;
        if (!is_null($prospection->children_total)) {
            $total_pop += $prospection->children_total;
        }
        if (!is_null($prospection->youth_total)) {
            $total_pop += $prospection->youth_total;
        }
        if (!is_null($prospection->adults_total)) {
            $total_pop += $prospection->adults_total;
        }
        if ($total_pop > 0) {
            $prospection->total_population = $total_pop;
        }
    }
}
