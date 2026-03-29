<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'pack_id'      => $this->pack_id,
            'name'         => $this->name,
            'description'  => $this->description,
            'created_at'   => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'deleted_at'   => $this->deleted_at,

            // This  maps each product to include its quantity from the pivot table
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id'              => $product->id,
                        'product_id'      => $product->product_id,
                        'name'            => $product->name,
                        'category_name'        => $product->category?->name,
                        'quantity'        => $product->pivot->quantity,
                    ];
                });
            }),
        ];
    }
}
