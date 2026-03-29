<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderItemResource extends JsonResource
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
            'delivery_order_id' => $this->delivery_order_id,
            'article_id' => $this->article_id,
            'article_name' => $this->article?->name,
            'expected_quantity' => $this->expected_quantity,
            
            
        ];
    }
}
