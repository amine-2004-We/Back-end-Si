<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProjectBankAccountResource extends JsonResource
{
    /**
     * Capitalize first letter UTF-8 safe
     */
    protected function mb_ucfirst(?string $string): ?string
    {
        if (!$string) return $string;
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'rib_iban' => $this->rib_iban,
            'account_title' => $this->account_title,
            'account_holder_name' => $this->mb_ucfirst($this->account_holder_name),
            'bic_swift' => $this->bic_swift,
            'opening_date' => $this->opening_date ? Carbon::parse($this->opening_date)->format('d-m-Y') : null,
            'opening_country' => $this->mb_ucfirst($this->opening_country),
            'currency' => $this->currency,
            'status' => $this->mb_ucfirst($this->status),
            'supporting_document' => $this->supporting_document,
            'comments' => $this->comments,
            'bank' => $this->mb_ucfirst($this->bank),
            'agency' => $this->mb_ucfirst($this->agency),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
        ];
    }
}
