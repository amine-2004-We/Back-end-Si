<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Collaborator;
use App\Models\Candidate;
use App\Models\External;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = 'unknown';
        $name = 'N/A';

        if ($this->resource instanceof Collaborator) {
            $type = 'collaborator';
            $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        } elseif ($this->resource instanceof Candidate) {
            $type = 'candidate';
            $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        } elseif ($this->resource instanceof External) {
            $type = 'external';
            $name = $this->full_name ?? 'N/A';
        }

        return [
            // We merge the entire original model's data
            ...parent::toArray($request),
            // And add our custom formatted fields
            'type' => $type,
            'display_name' => $name,
        ];
    }
}
