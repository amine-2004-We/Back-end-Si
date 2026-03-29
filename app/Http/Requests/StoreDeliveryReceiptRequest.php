<?php

namespace App\Http\Requests;

use App\Enums\DeliveryReceiptStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryReceiptRequest extends FormRequest
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
            'required',
            'integer',
            Rule::exists('delivery_orders', 'id')
            ->whereIn('status', ['Émis'])
            ->withoutTrashed(),
        ],
            'reception_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'receiver_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'storage_location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                Rule::enum(DeliveryReceiptStatusEnum::class),
            ],
            'observations' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
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
