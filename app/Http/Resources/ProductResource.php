<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'category_name'  => $this->category?->name,
            'product_type_name' => $this->productType?->name,
            'product_type_id'=> $this->product_type_id,
            'deleted_at' => $this->deleted_at
        ];
    }
}
