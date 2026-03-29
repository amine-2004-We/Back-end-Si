<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'purchase_request_id' => $this->purchase_request_id,
            'purchase_order_id' => $this->purchase_order_id,
            'applicant' => $this->applicant,
            'recipient' => $this->recipient, 
            'recipient_contact' => $this->recipient_contact, 
            'request_date' => $this->request_date,
            'request_purpose' => $this->request_purpose,
            'delivery_location' => $this->delivery_location,
            'delivery_date' => $this->delivery_date,
            'priority' => $this->priority,
            'status' => $this->status,
            'observations' => $this->observations,
            'superior_id'=>$this->creator?->collaborator?->superior?->user?->id,
            'purchase_request' => $this->whenLoaded('purchaseRequest'),
            'purchase_order' => $this->whenLoaded('purchaseOrder'),
            'applicant_details' => $this->whenLoaded('applicant'),
            'recipient_details' => $this->whenLoaded('recipient'), 
            'creator' => $this->whenLoaded('creator'),
            // Add quote from purchaseOrder if exists
            'quote' => $this->purchaseOrder && $this->purchaseOrder->quote ? $this->purchaseOrder->quote : null,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'article_id' => $item->article_id,
                        'quantity_requested' => $item->quantity_requested,
                        'article' => $item->article, // Suppression de whenLoaded ici
                    ];
                });
            }),
        ];
    }
}