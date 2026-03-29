<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyAccountResource extends JsonResource
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
            'name' => $this->name,
            // Use the accessor from the model to get the fully formatted code
            'code' => $this->code,
            'subdivision' => $this->subdivision,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Conditionally include the parent general account
            'general_account' => new GeneralAccountResource($this->whenLoaded('generalAccount')),
        ];
    }
}
