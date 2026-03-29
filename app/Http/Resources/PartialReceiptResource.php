<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
// Assuming you have or will create this resource for DeliveryReceiptItem
use App\Http\Resources\DeliveryReceiptItemResource; 

class PartialReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pv_partial_id' => $this->pv_partial_id,
            'delivery_receipts' => $this->whenLoaded('deliveryReceipts', function () {
                return $this->deliveryReceipts->map(function ($dr) {
                    return [
                        'id' => $dr->id,
                        'name' => $dr->name ?? $dr->receipt_identifier,
                        'receipt_identifier' => $dr->receipt_identifier,
                    ];
                });
            }),
            'received_at' => $this->received_at->format('Y-m-d'),
            'observations' => $this->observations,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'deliveryReceipts_deliveryOrder_purchaseOrder' => $this->whenLoaded('deliveryReceipts', function () {
                return $this->deliveryReceipts->map(function ($dr) {
                    return optional($dr->deliveryOrder)->purchaseOrder;
                });
            }),
            'deliveryReceipts_items' => $this->whenLoaded('deliveryReceipts', function () {
                return $this->deliveryReceipts->map(function ($dr) {
                    return $dr->items;
                });
            }),
            'deliveryReceipts_receiver' => $this->whenLoaded('deliveryReceipts', function () {
                return $this->deliveryReceipts->map(function ($dr) {
                    return $dr->receiver;
                });
            }),
        ];
    }
}

