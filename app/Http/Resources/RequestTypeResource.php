<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestTypeResource extends JsonResource
{
    public function toArray(Request $request):array
    {
        return [
            'type_name'=>$this->type_name,
            'created_at' => $this->created_at,
        ];
    }

}
