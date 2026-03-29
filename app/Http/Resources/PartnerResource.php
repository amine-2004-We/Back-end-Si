<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partner_name' => $this->partner_name,
            'abbreviation' => $this->abbreviation,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'country' => $this->country,
            'partner_logo' => $this->partner_logo,
            'logo_url' => $this->partner_logo ? asset('storage/' . $this->partner_logo) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'partner_type' => $this->partner_type,
            'created_by_id' => $this->created_by_id,
            'creator_name' => $this->whenLoaded('creator', $this->creator ? $this->creator->name : null),

            'nature_partner' => $this->whenLoaded('naturePartner', $this->naturePartner ? $this->naturePartner->name : null),
            'structure_partner' => $this->whenLoaded('structurePartner', $this->structurePartner ? $this->structurePartner->name : null),
            'status' => $this->whenLoaded('status', $this->status ? $this->status->name : null),

            'nature_partner_id' => $this->nature_partner_id,
            'structure_partner_id' => $this->structure_partner_id,
            'status_id' => $this->status_id,

            'date_debut_partenariat' => $this->date_debut_partenariat,
            'closure_reason' => $this->closure_reason,

            'contact_people' => ContactPersonResource::collection($this->whenLoaded('contactPeople')),
            'notes' => PartnerNoteResource::collection($this->whenLoaded('notes')),

            'deleted_at' => $this->deleted_at
        ];
    }
}
