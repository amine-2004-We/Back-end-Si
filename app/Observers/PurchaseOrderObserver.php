<?php

namespace App\Observers;

use App\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        $year = now()->format('Y');

        $count = PurchaseOrder::withTrashed()->count() + 1;
        $purchaseOrder->po_number = sprintf('BC-%s-%04d', $year, $count);

        if (empty($purchaseOrder->status)) {
            $purchaseOrder->status = 'draft';
        }
    }
}
