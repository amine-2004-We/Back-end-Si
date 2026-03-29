<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
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
            'candidate_id' => $this->candidate_id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'last_name_ar' => $this->last_name_ar,
            'first_name_ar' => $this->first_name_ar,
            'email' => $this->email,
            'cin' => $this->cin,
            'cnss' => $this->cnss,
            'title' => $this->title,
            'phone' => $this->phone,
            'rib' => $this->rib,
            'birth_date' => $this->birth_date,
            'birth_region_id'=>$this->birth_region_id,
            'birth_province_id' => $this->birth_province_id,
            'residence_address' => $this->residence_address,
            'residence_region_id'=>$this->residence_region_id,
            'residence_province_id' => $this->residence_province_id,
            'source' => $this->source,
            'status' => $this->status,
            'total_experience' => $this->total_experience,
            'educational_experience' => $this->educational_experience,
            'marital_status' => $this->marital_status,
            'number_of_children'=>$this->number_of_children,
            'education_level' => $this->education_level,
            'discipline' => $this->discipline,
            'institution' => $this->institution,
            'graduation_date' => $this->graduation_date,
            'job_posting_id'=>$this->job_posting_id,
            'job_posting_name'=>$this->jobPosting?->name,
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
