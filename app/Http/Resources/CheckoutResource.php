<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'code'=>$this->code,
            'project_id'=>$this->project_id,
            'initiale_amount'=>$this->initiale_amount,
            'used_amount'=>$this->used_amount,
            'available_balance'=>$this->available_balance,
            'collaborator_id'=>$this->collaborator_id
        ];
    }
}
