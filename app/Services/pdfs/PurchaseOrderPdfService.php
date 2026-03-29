<?php

namespace App\Services\Pdfs;

use App\Models\PurchaseOrder;
use Spatie\Browsershot\Browsershot;

class PurchaseOrderPdfService
{
    public function generate(PurchaseOrder $po): string
    {
        $html = view('pdfs.purchase-order', [
            'po' => $po
        ])->render();
        $directory = storage_path('app/public/purchase-orders');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = storage_path(
            "app/public/purchase-orders/purchase-order-{$po->id}.pdf"
        );

        Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);

        return $path;
    }
}
