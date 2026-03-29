<?php

namespace App\Http\Requests;

use App\Enums\DeliveryReceiptStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_order_id' => [
            'sometimes',
            'integer',
            Rule::exists('delivery_orders', 'id')
            ->whereIn('status', ['Émis'])
            ->withoutTrashed(),
        ],
            'receiver_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'reception_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'storage_location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::enum(DeliveryReceiptStatusEnum::class),
            ],
            'observations' => [
                'nullable',
                'string',
            ],
            'items' => [
            'sometimes',
            'array',
            'min:1',
            ],
            'items.*.article_id' => [
                'required',
                'integer',
                Rule::exists('articles', 'id')->withoutTrashed(),
            ],
            'items.*.quantity_received' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
