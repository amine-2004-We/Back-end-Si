<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralAccountResource extends JsonResource
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
            // Use the accessor from the model to get the formatted code
            'code' => $this->code,
            'class' => $this->class,
            'account' => $this->account,
            'sub_account' => $this->sub_account,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Conditionally include the related third-party accounts
            'third_party_accounts' => ThirdPartyAccountResource::collection($this->whenLoaded('thirdPartyAccounts')),
        ];
    }
}
