<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PartialReceiptStatusEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule; // Added

class StorePartialReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Validation personnalisée pour empêcher l'association multiple d'un même bon de réception.
     */
    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         $deliveryReceiptIds = $this->input('delivery_receipt_ids', []);
    //         if (!empty($deliveryReceiptIds)) {
    //             $alreadyLinked = \DB::table('delivery_receipt_partial_receipt')
    //                 ->whereIn('delivery_receipt_id', $deliveryReceiptIds)
    //                 ->exists();
    //             if ($alreadyLinked) {
    //                 $validator->errors()->add('delivery_receipt_ids', 'Un ou plusieurs bons de réception sont déjà associés à un autre PV partiel. Veuillez choisir des bons de réception non utilisés.');
    //             }
    //         }
    //     });
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_receipt_ids' => ['required', 'array', 'min:1'],
            'delivery_receipt_ids.*' => ['required', 'integer', 'exists:delivery_receipts,id'],
            'received_at' => 'required|date|before_or_equal:today',
            'status' => ['required', new Enum(PartialReceiptStatusEnum::class)],
            'observations' => 'nullable|string',
        ];
    }
}
