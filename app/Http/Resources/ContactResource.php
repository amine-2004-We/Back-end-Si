<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'contact_code'      => $this->contact_code,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'organisation'      => $this->organisation,
            'position'          => $this->position,
            'original_channel'  => $this->original_channel?->value,
            'acquisition_date'  => $this->acquisition_date,
            'contact_status'    => $this->contact_status?->value,
            'option'            => $this->option,
            'companion'         => $this->companion,
            'consent_date'      => $this->consent_date,
            'tags'              => $this->tags,
            'api'               => $this->api,
            'created_by'        => $this->created_by,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'deleted_at'        => $this->deleted_at,
        ];
    }
}
