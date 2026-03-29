<?php

namespace App\Services\pdfs;

use App\Models\DeliveryRequest;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class DeliveryRequestPdfService
{
    /**
     * Génère le PDF de la demande de livraison et retourne le chemin du fichier.
     *
     * @param int $id
     * @return string
     */
    public function generatePdf(int $id): string
    {
        // Charger la demande de livraison avec toutes ses relations
        $deliveryRequest = DeliveryRequest::with([
            'purchaseOrder',
            'purchaseRequest',
            'applicant',
            'recipient',
            'creator',
            'items.article', // Charger l'article pour chaque item
        ])->findOrFail($id);

        // Générer le HTML à partir de la vue
        $html = View::make('pdf.delivery_request', [
            'deliveryRequest' => $deliveryRequest,
        ])->render();

        // Créer le répertoire s'il n'existe pas
        $directory = storage_path('app/public/delivery-requests');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Définir le chemin du fichier PDF
        $path = storage_path(
            "app/public/delivery-requests/delivery-request-{$deliveryRequest->id}.pdf"
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
