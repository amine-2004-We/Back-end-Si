<?php

namespace App\Http\Requests;

use App\Enums\DeliveryRequestStatus;
use App\Enums\PriorityFcEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_request_id' => ['required', 'exists:purchase_requests,id'],
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'applicant' => ['required', 'exists:collaborators,id'],
            'recipient' => ['required', 'exists:collaborators,id'], 
            'recipient_contact' => ['nullable', 'string', 'max:255'],
            'request_date' => ['required', 'date'],
            'request_purpose' => ['required', 'string'],
            'delivery_location' => ['required', 'string'],
            'delivery_date' => ['nullable', 'date'],
            'priority' => ['nullable', new Enum(PriorityFcEnum::class)],
            'status' => ['sometimes', new Enum(DeliveryRequestStatus::class)],
            'observations' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.article_id' => ['required', 'exists:articles,id'],
            'items.*.quantity_requested' => ['required', 'integer', 'min:1'],
        ];
    }

    public function prepareForValidation()
    {
        if (!$this->has('request_date')) {
            $this->merge([
                'request_date' => now()->toDateString(),
            ]);
        }

        if (!$this->has('status')) {
            $this->merge([
                'status' => 'pending',
            ]);
        }
    }
}