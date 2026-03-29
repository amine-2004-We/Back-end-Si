<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TrainingGroupCollection extends ResourceCollection
{
    public $collects = TrainingGroupResource::class;

    public function toArray($request): array
    {
        // Keep default structure: data + links + meta when a paginator is used
        return [
            'data' => $this->collection,
        ];
    }

    // Optionally, customize pagination structure if desired by overriding paginationInformation
    // public function paginationInformation($request, $paginated, $default): array
    // {
    //     return $default; // or mutate structure as needed
    // }
}
