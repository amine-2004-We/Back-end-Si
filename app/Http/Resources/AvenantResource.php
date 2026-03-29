<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CalltenderResource;

class AvenantResource extends JsonResource
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
            'avenant_id' => $this->avenant_id,
            'marche_id' => $this->marche_id,
            'marche' => CalltenderResource::make($this->whenLoaded('marche')),
            'responsible_id' => $this->responsible_id,
            'responsible_name' => $this->responsible ? ($this->responsible->first_name . ' ' . $this->responsible->last_name) : null,
            'subject' => $this->subject,
            'modification_nature' => $this->modification_nature,
            'additional_amount' => $this->additional_amount,
            'new_end_date' => $this->new_end_date,
            'document_path' => $this->document_path,
            'status' => $this->status,
            'signature_date' => $this->signature_date,
            'observations' => $this->observations,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

