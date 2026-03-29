<?php

namespace App\Http\Resources\Options;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainerOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        $trainer  = $this->resource;

        $internal = $trainer->relationLoaded('internalTrainer') ? $trainer->internalTrainer : null;
        $collab   = $internal?->collaborator;

        $external = $trainer->relationLoaded('externalTrainer') ? $trainer->externalTrainer : null;

        $first = null;
        $last  = null;
        $label = null;
        $type  = $trainer->type ?? null;

        if ($collab) {
            $type  = $type ?: 'internal';
            $code  = $collab->collaborator_code ?? $collab->code ?? null;
            $first = $collab->first_name ?? null;
            $last  = $collab->last_name  ?? null;

            $name  = $this->fullName($first, $last);
            $label = $this->joinLabel($code, $name);
        }
        elseif ($external) {
            $type  = $type ?: 'external';

            $code  = $external->trainer_identifier ?? null;
            $name  = $external->full_name ?: null;

            $label = $this->joinLabel($code, $name);
        }

        if (!$label) {
            $fallbackName = $this->fullName($trainer->first_name ?? null, $trainer->last_name ?? null)
                ?: ($trainer->name ?? null);
            $label = $fallbackName ?: ("Formateur #{$trainer->id}");
        }

        return [
            'id'         => (int) $trainer->id,
            'type'       => $type,
            'label'      => $label,
            'first_name' => $first,
            'last_name'  => $last,
        ];
    }

    private function fullName(?string $first, ?string $last): ?string
    {
        $name = trim(($first ?? '') . ' ' . ($last ?? ''));
        return $name !== '' ? $name : null;
    }

    private function joinLabel(?string $code, ?string $name): ?string
    {
        $parts = [];
        if ($code !== null && $code !== '') $parts[] = $code;
        if ($name !== null && $name !== '') $parts[] = $name;
        return $parts ? implode(' — ', $parts) : null;
    }
}
