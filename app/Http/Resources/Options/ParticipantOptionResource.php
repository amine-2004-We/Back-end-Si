<?php

namespace App\Http\Resources\Options;

use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantOptionResource extends JsonResource
{
   public function toArray($request)
    {
        $tc   = $this->whenLoaded('traineeCollaborator');
        $coll = $tc?->collaborator;

        $ext  = $this->whenLoaded('externalTrainee');

        $label = null;
        $first = null;
        $last  = null;
        $cin   = null;

        if ($coll) {
            $code  = $coll->collaborator_code ?? $coll->code ?? null;
            $first = $coll->first_name ?? null;
            $last  = $coll->last_name  ?? null;
            $cin   = $coll->cin ?? null;

            $fullname = trim(($first ?? '').' '.($last ?? ''));
            $label = $code
                ? trim($code . ' — ' . ($fullname !== '' ? $fullname : ''))
                : ($fullname !== '' ? $fullname : null);
        } elseif ($ext) {
            $code  = $ext->external_code ?? $ext->trainer_code ?? $ext->code ?? null;

            $fullname = trim(($ext->full_name ?? ''));
            $base = $fullname !== '' ? $fullname : ($fullname ?: null);

            $label = $code ? trim($code . ' — ' . ($base ?? '')) : ($base ?? null);
        }

        $keywords = collect([$cin, $label])->filter()->implode(' ');

        return [
            'id'                  => (int) $this->id,
            'attendance_recorded' => (bool) $this->attendance_recorded,
            'insured'             => (bool) $this->insured,
            'comments'            => $this->comments,
            'label'               => $label,
            'cin'                 => $cin,
            'keywords'            => $keywords, 
        ];
    }
}
