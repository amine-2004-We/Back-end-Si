<?php

namespace App\Observers;

use App\Models\BudgetLine;

/**
 * class BudgetLigneObserver
 */
class BudgetLigneObserver
{
    /**
     * @param BudgetLine $budgetLigne
     * @return void
     */
    public function creating(BudgetLine $budgetLigne): void
    {
        $this->generateCode($budgetLigne);
    }

    public function updating(BudgetLine $budgetLigne): void
    {
        $this->generateCode($budgetLigne);
    }

    /**
     * Générateur de code réutilisable
     */
    private function generateCode(BudgetLine $budgetLigne): void
    {
        $year = date('Y');

        if (!$budgetLigne->relationLoaded('category')) {
            $budgetLigne->load('category');
        }

        $categoryCode = $budgetLigne->category?->code ?? 'XXX';

        $count = BudgetLine::withTrashed()->count() + 1;

        $budgetLigne->code = sprintf('LBG-%s-%s-%03d', $year, $categoryCode, $count);
    }
}
