<?php
namespace App\Services\pdfs;

use App\Models\ProvisionalAcceptance;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class ProvisionalAcceptancePdfService
{
    /**
     * Génère le PDF du PV de réception provisoire et retourne le chemin du fichier.
     *
     * @param int $id
     * @return string
     */
    public function generatePdf(int $id): string
    {
        // Récupérer le PV avec ses relations
        $pv = ProvisionalAcceptance::with([
            'items.article',
            'callTender',
            // Ajoutez ici d'autres relations si nécessaire
        ])->findOrFail($id);

        // Générer le HTML à partir de la vue
        $html = View::make('pdf.provisional_acceptance', [
            'pv' => $pv,
            'items' => $pv->items,
        ])->render();

        // Créer le répertoire s'il n'existe pas
        $directory = storage_path('app/public/provisional-acceptances');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Définir le chemin du fichier PDF
        $path = storage_path(
            "app/public/provisional-acceptances/provisional-acceptance-{$pv->id}.pdf"
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