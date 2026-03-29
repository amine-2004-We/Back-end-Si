<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgrammePedagogiqueE2CNGResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'groupe_id' => $this->groupe_id,
            'project_id' => $this->project_id,
            'type' => $this->type, 
            'professional_option' => $this->professional_option, 
            
            'project_pedagogique' => $this->project_pedagogique,
            'project_pedagogique_arabe' => $this->project_pedagogique_arabe,
            
            'metier' => $this->metier,
            'metier_arabe' => $this->metier_arabe,
            
            'ateliers' => $this->ateliers,
            'ateliers_arabe' => $this->ateliers_arabe,
            
            'observation' => $this->observation,
            
            'date_prevu' => $this->date_prevu,
            'date_realisation' => $this->date_realisation,
            'date_prevue' => $this->date_prevue, 
            'real_start_date' => $this->real_start_date, 
            'real_end_date' => $this->real_end_date, 
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            
            'groupe' => $this->whenLoaded('groupe', function() {
                return [
                    'id' => $this->groupe->id,
                    'name' => $this->groupe->name
                ];
            }),
            
            'projet' => $this->whenLoaded('project', function() {
                return [
                    'id' => $this->project->id,
                    'project_name' => $this->project->project_name ?? $this->project->name
                ];
            }),
        ];
    }
}