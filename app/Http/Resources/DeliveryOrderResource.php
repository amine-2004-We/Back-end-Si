<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'delivery_request_id' => $this->delivery_request_id,
            'delivery_request' => new \App\Http\Resources\DeliveryRequestResource($this->whenLoaded('deliveryRequest')),
            'id' => $this->id,
            'order_id' => $this->order_id,
            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order_number' => $this->purchaseOrder?->po_number,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier?->company_name,
            'quote_id' => $this->quote_id,
            'quote_name' => $this->quote?->quote_number,
            'delivery_address' => $this->delivery_address,
            'expected_delivery_date' => $this->expected_delivery_date,
            'comments' => $this->comments,
            'status' => $this->status,
            'items' => DeliveryOrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->creator?->name,
            'creator_id' => $this->created_by,
            'superior_id'=>$this->creator?->collaborator?->superior?->user?->id,
        ];
    }
}
