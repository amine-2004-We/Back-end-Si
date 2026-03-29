<?php

namespace App\Services\pdfs;

use App\Models\PartialReceipt;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

class PartialReceiptPdfService
{
    /**
     * Génère le PDF du PV Partiel et retourne le chemin du fichier
     */
    public function generatePdf(int $id): string
    {

        $partialReceipt = PartialReceipt::with([
            'deliveryReceipts.deliveryOrder.purchaseOrder',
            'deliveryReceipts.items',
            'deliveryReceipts.receiver',
        ])->findOrFail($id);


        $html = View::make('pdf.partial_receipt', [
            'partialReceipt' => $partialReceipt
        ])->render();

        $directory = storage_path('app/public/partial-receipts');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }


        $path = storage_path(
            "app/public/partial-receipts/partial-receipt-{$partialReceipt->id}.pdf"
        );

        Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);

        return $path;
    }
}
