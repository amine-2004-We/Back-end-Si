<?php

namespace App\Http\Requests;

use App\Enums\ProvisionalAcceptanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProvisionalAcceptanceRequest extends FormRequest
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
            'delivery_receipt_id' => ['required', 'integer', Rule::exists('delivery_receipts', 'id')->withoutTrashed()],
            'provisional_acceptance_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(ProvisionalAcceptanceStatusEnum::class)],
            'reserves' => ['nullable', 'string'],
            'corrective_actions' => ['nullable', 'string'],
            'calltender_id' => ['required', 'integer', Rule::exists('calltenders', 'id')->withoutTrashed()],
            'partial_receipt_ids' => ['required', 'array', 'min:1'],
            'partial_receipt_ids.*' => ['required', 'integer', Rule::exists('partial_receipts', 'id')->withoutTrashed()],
            'committee_ids' => ['required', 'array', 'min:1'],
            'committee_ids.*' => ['required', 'integer', Rule::exists('users', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.article_id' => ['required', 'integer', Rule::exists('articles', 'id')->withoutTrashed()],
            'items.*.quantity_received' => ['required', 'integer', 'min:1'],
        ];
    }
}
