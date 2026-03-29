<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\PurchaseList;
use Illuminate\Support\Facades\DB;

class LoadPurchaseListService
{
    /**
     * Charger tous les articles actifs d'une liste d'achat dans un devis
     *
     * @param Quote $quote
     * @param int $purchaseListId
     * @return array
     */
    public function loadArticles(Quote $quote, int $purchaseListId): array
    {
        // 1. Récupérer la PurchaseList
        $purchaseList = PurchaseList::findOrFail($purchaseListId);

        // 2. Mettre à jour le devis avec la purchase_list_id
        $quote->update(['purchase_list_id' => $purchaseListId]);

        return DB::transaction(function () use ($quote, $purchaseList) {
            // 3. Récupérer les articles ACTIFS de la liste
            $items = $purchaseList->items()
                ->where('is_active', true)
                ->with('article')
                ->get();

            // 4. Supprimer les anciennes lignes (si existantes)
            $quote->items()->delete();

            // 5. Créer les nouvelles lignes
            $createdLines = [];
            foreach ($items as $item) {
                $article = $item->article;

                // Vérifier que l'article existe et est actif
                if (!$article || !$article->is_active) {
                    continue;
                }

                $quoteItem = $quote->items()->create([
                    'article_id' => $article->id,
                    'quantity' => 0, // Quantité à remplir par l'utilisateur
                    'unit_price_ht' => $article->price_ht ?? 0,
                    'tva_rate' => $article->vat_rate ?? 0,
                ]);

                $createdLines[] = $quoteItem;
            }

            // 6. Recalculer les totaux du devis
            $quote->recomputeTotals();
            $quote->save();

            return [
                'success' => true,
                'quote_id' => $quote->id,
                'purchase_list_id' => $purchaseList->id,
                'lines_count' => count($createdLines),
                'message' => count($createdLines) . ' article(s) chargé(s) avec succès',
            ];
        });
    }
}
