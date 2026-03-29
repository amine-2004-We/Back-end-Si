<?php

namespace App\Services\pdfs;

use App\Models\FinalAcceptance;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class FinalAcceptancePdfService
{
    /**
     * Génère le PDF du PV de réception définitive et retourne le chemin du fichier.
     *
     * @param int $id
     * @return string
     */
    public function generatePdf(int $id): string
    {
        // Récupérer le PV définitif avec ses relations
        $pv = FinalAcceptance::with([
            'articles',
            'calltender',
            'committeeMembers',
            'provisionalAcceptances'
        ])->findOrFail($id);

        // Calculer la quantité globale reçue pour chaque article via les PV provisoires liés
        $articleQuantities = [];
        foreach ($pv->articles as $article) {
            $totalReceived = 0;
            foreach ($pv->provisionalAcceptances as $provisional) {
                // Charger les items pour chaque PV provisoire
                foreach ($provisional->items as $item) {
                    if ($item->article_id == $article->id) {
                        $totalReceived += $item->quantity_received;
                    }
                }
            }
            $articleQuantities[$article->id] = $totalReceived;
        }

        // Générer le HTML à partir de la vue
        $html = View::make('pdf.final_acceptance', [
            'pv' => $pv,
            'articles' => $pv->articles,
            'articleQuantities' => $articleQuantities,
        ])->render();

        // Créer le répertoire s'il n'existe pas
        $directory = storage_path('app/public/final-acceptances');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Définir le chemin du fichier PDF
        $path = storage_path(
            "app/public/final-acceptances/final-acceptance-{$pv->id}.pdf"
        );

        // Générer le PDF avec Browsershot
        Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);

        return $path;
    }
}