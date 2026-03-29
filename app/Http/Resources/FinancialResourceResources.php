<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialResourceResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'financial_resources_code'=>$this->financial_resources_code,
            'financial_resources_type'=>$this->financial_resources_type,
            'partner_id'=>$this->partner_id,
            'project_id'=>$this->project_id,
            'slice'=>$this->slice,
            'amount_received'=>$this->amount_received,
            'slice_date'=>$this->slice_date,
            'currency'=>$this->currency,
            'financial_type'=>$this->financial_type,
            'signature_date'=>$this->signature_date,
            'project_start_date'=>$this->project_start_date,
            'project_end_date'=>$this->project_end_date,
            'financial_status'=>$this->financial_status,
            'attachment'=>$this->attachment,
            'comments'=>$this->comments
        ];
    }
}
