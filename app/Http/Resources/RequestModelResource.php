<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestModelResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'pattern'=>$this->pattern,
            'amount'=>$this->amount,
            'month'=>$this->month,
            'collaborator_id'=>$this->collaborator_id,
            'request_type_id'=>$this->request_type_id,
            'request_status'=>$this->request_status
        ];
    }

}
