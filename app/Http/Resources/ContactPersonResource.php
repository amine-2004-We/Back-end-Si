<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactPersonResource extends JsonResource
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
            'last_name' => $this->last_name, 
            'first_name' => $this->first_name, 
            'position' => $this->position, 
            'first_name_arabic' => $this->first_name_arabic,
            'last_name_arabic' => $this->last_name_arabic,
            'phone' => $this->phone, 
            'email' => $this->email, 
            'address' => $this->address, 
            'partner' => [
                'id' => $this->partner?->id,
                'partner_name' => $this->partner?->partner_name,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}