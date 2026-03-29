<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryReceiptItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $article = $this->whenLoaded('article');

        return [
             'id' => $article->id ?? null,
            'name' => $article->name ?? 'Article not found',
            'reference' => $article->reference ?? $article->article_id ?? 'N/A', // Use reference or article_id

             'quantity_received' => $this->quantity_received,
 
            'pivot' => [
                'quantity_received' => $this->quantity_received,
            ]
        ];
    }
}
