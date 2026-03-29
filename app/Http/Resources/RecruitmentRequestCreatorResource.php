<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruitmentRequestCreatorResource extends JsonResource
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
            'email' => $this->email,
            'roles' => $this->roles->pluck('name'),

            'collaborator' => $this->whenLoaded('collaborator', function () {
                return [
                    'id' => $this->collaborator->id,
                    'first_name' => $this->collaborator->first_name,
                    'last_name' => $this->collaborator->last_name,

                    'superior' => $this->collaborator->superior
                        ? [
                            'id' => $this->collaborator->superior->id,
                            'first_name' => $this->collaborator->superior->first_name,
                            'last_name' => $this->collaborator->superior->last_name,
                            'user' => $this->collaborator->superior->user
                                ? [
                                    'id' => $this->collaborator->superior->user->id,
                                    'name' => $this->collaborator->superior->user->name,
                                    'email' => $this->collaborator->superior->user->email,
                                    'roles' => $this->roles->pluck('name'),

                                ]
                                : null,
                        ]
                        : null,
                ];
            }),
        ];
        }
}
