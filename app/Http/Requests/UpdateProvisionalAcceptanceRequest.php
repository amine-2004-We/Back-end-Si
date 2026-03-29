<?php

namespace App\Http\Requests;

use App\Enums\ProvisionalAcceptanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvisionalAcceptanceRequest extends FormRequest
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
            'delivery_receipt_id' => ['prohibited'],
            'calltender_id' => ['prohibited'],
            'provisional_acceptance_date' => ['sometimes', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'status' => ['sometimes', 'required', Rule::enum(ProvisionalAcceptanceStatusEnum::class)],
            'reserves' => ['nullable', 'string'],
            'corrective_actions' => ['nullable', 'string'],
            'partial_receipt_ids' => ['sometimes', 'array', 'min:1'],
            'partial_receipt_ids.*' => ['required', 'integer', Rule::exists('partial_receipts', 'id')->withoutTrashed()],
            'committee_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'committee_ids.*' => ['required', 'integer', Rule::exists('users', 'id')],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.article_id' => ['required', 'integer', Rule::exists('articles', 'id')->withoutTrashed()],
            'items.*.quantity_received' => ['required', 'integer', 'min:1'],
        ];
    }
}
