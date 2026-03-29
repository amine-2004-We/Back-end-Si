<?php

namespace App\Services\pdfs;

use App\Models\ServiceOrder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class ServiceOrderPdfService
{
    public function generatePdf(int $id): string
    {
        $serviceOrder = ServiceOrder::with([
            'purchaseOrder',
            'calltender',
            'supplier',
            'supervisor',
            // Ajoutez ici d'autres relations nécessaires
        ])->findOrFail($id);

        $html = View::make('pdfs.service-order',[
            'serviceOrder' => $serviceOrder
        ])->render();

        $directory = storage_path('app/public/service-orders');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $path = storage_path(
            "app/public/service-orders/service-order-{$serviceOrder->id}.pdf"
        );
        \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($path);
        return $path;
    }
}
