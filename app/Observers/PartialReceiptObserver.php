<?php

namespace App\Observers;

use App\Models\PartialReceipt;

class PartialReceiptObserver
{
    /**
     * Handle the PartialReceipt "creating" event.
     * Generates ID: PVRP-[ID DR]-[Numéro]
     */
    public function creating(PartialReceipt $partialReceipt): void
{
    if (is_null($partialReceipt->pv_partial_id)) {
        // On récupère le premier ID du tableau envoyé par le formulaire
        $firstDRId = request()->input('delivery_receipt_ids.0');

        // On compte via la table pivot "delivery_receipt_partial_receipt"
        // Laravel le fait automatiquement grâce à whereHas
        $count = PartialReceipt::withTrashed()
            ->whereHas('deliveryReceipts', function($q) use ($firstDRId) {
                $q->where('delivery_receipts.id', $firstDRId);
            })
            ->count() + 1;
        
        $drIdentifier = $firstDRId ?? '000';
        $partialReceipt->pv_partial_id = 'PVRP-' . $drIdentifier . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
}