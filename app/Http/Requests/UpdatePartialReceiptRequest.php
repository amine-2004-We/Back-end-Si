<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PartialReceiptStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule; // Added

class UpdatePartialReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handled by controller/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $partialReceiptId = $this->route('id');  
        return [
            'delivery_receipt_ids' => ['sometimes', 'array', 'min:1'],
            'delivery_receipt_ids.*' => ['required', 'integer', 'exists:delivery_receipts,id'],
            'received_at' => 'sometimes|date|before_or_equal:today',
            'status' => ['sometimes', new Enum(PartialReceiptStatusEnum::class)],
            'observations' => 'nullable|string',
        ];
    }
}
