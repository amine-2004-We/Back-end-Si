<?php

namespace App\Http\Requests;

use App\Enums\ServiceOrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceOrderRequest extends FormRequest
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
            'purchase_order_id' => [
                'nullable',
                'integer',
                Rule::exists('purchase_orders', 'id')->withoutTrashed(),
                'required_without:calltender_id',
            ],
            'calltender_id' => [
                'nullable',
                'integer',
                Rule::exists('calltenders', 'id'),
                'required_without:purchase_order_id',
            ],
            'subject' => [
                'required',
                'string',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'estimated_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'supplier_id' => [
                'required',
                'integer',
                Rule::exists('suppliers', 'id')->withoutTrashed(),
            ],
            'supervisor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'signed_document' => [
                'required',
                'file',
                'mimes:pdf',
            ],
            'status' => [
                'required',
                Rule::enum(ServiceOrderStatusEnum::class),
            ],
            'observations' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Format date conversion
        if ($this->has('start_date') && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->start_date)) {
            try {
                $this->merge([
                    'start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $this->start_date)->format('Y-m-d'),
                ]);
            } catch (\Exception $e) { /* Let validation handle format error */ }
        }

        if ($this->has('estimated_end_date') && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->estimated_end_date)) {
           try {
                $this->merge([
                    'estimated_end_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $this->estimated_end_date)->format('Y-m-d'),
                ]);
           } catch (\Exception $e) { /* Let validation handle format error */ }
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasPurchaseOrder = $this->filled('purchase_order_id');
            $hasCalltender = $this->filled('calltender_id');
            
            if ($hasPurchaseOrder && $hasCalltender) {
                $validator->errors()->add('source', 'Vous ne pouvez pas sélectionner à la fois un bon de commande et un marché.');
            }
            
            if (!$hasPurchaseOrder && !$hasCalltender) {
                $validator->errors()->add('source', 'Vous devez sélectionner soit un bon de commande, soit un marché.');
            }
        });
    }
}