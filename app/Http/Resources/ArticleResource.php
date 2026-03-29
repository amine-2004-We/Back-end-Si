<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'article_id' => $this->article_id,
            'product_id' => $this->product_id,
            'product_name' =>  $this->product?->name,
            'reference_price' => $this->reference_price,
            'brand' => $this->brand,
            'unit' => $this->unit,
            'specifications' => $this->specifications,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at
        ];
    }
}
