<?php

namespace App\Services\pdfs;

use App\Models\PurchaseList;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class PurchaseListPdfService
{
    /**
     * Génère le PDF de la liste d'achats (preview ou download)
     * @param PurchaseList $purchaseList
     * @param bool $download
     * @return \Illuminate\Http\Response
     */
    /**
     * Génère le PDF de la liste d'achats et retourne le chemin du fichier
     * @param int $id
     * @return string
     */
    public function generatePdf(int $id): string
    {
        $purchaseList = PurchaseList::with([
            'requests.project',
            'items.product',
            'createdBy',
            'quotes.items',
            'quotes.supplier',
            'department',
        ])->findOrFail($id);

        $html = View::make('pdf.purchase_list', [
            'purchaseList' => $purchaseList
        ])->render();

        $directory = storage_path('app/public/purchase-lists');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // 0775 → écriture contrôlée  (recommandé en prod)
        }
        $path = storage_path(
            "app/public/purchase-lists/purchase-list-{$purchaseList->id}.pdf"
        );
        Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);
        return $path;
    }
}
