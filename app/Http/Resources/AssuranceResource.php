<?php

namespace App\Http\Resources;

use App\Models\Candidate;
use App\Models\External;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssuranceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $personneAssuree = $this->whenLoaded('personneAssuree');
        $personneName = 'N/A';
        $personneTypeLabel = 'Inconnu';

        if ($personneAssuree) {
            if ($personneAssuree instanceof Candidate) {
                $personneName = $personneAssuree->first_name . ' ' . $personneAssuree->last_name;
                $personneTypeLabel = 'Candidat';
            } elseif ($personneAssuree instanceof External) {
                $personneName = $personneAssuree->full_name;
                $personneTypeLabel = 'Externe';
            }
        }

        return [
            'id' => $this->id,
            'insurance_id' => $this->insurance_id,
            'personne_assuree_details' => [
                'id' => $personneAssuree?->id,
                'name' => $personneName,
                'type_label' => $personneTypeLabel,
                'type_class' => $this->personne_assuree_type,
            ],
            'personne_assuree' => $personneAssuree,
            'insurance_type' => $this->insurance_type,
            'insurance_organization' => $this->insurance_organization,
            'affiliation_date' => $this->affiliation_date->format('Y-m-d'),
            'termination_date' => $this->termination_date?->format('Y-m-d'),
            'comments' => $this->comments,
            'created_at' => $this->created_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'status' => $this->status,
        ];
    }
}

