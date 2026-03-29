<?php

namespace App\Http\Requests;

use App\Enums\ServiceOrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends FormRequest
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
                'exists:purchase_orders,id',
                'required_without:calltender_id',
            ],
            'calltender_id' => [
                'nullable',
                'integer',
                'exists:calltenders,id',
                'required_without:purchase_order_id',
            ],
            'subject' => [
                'sometimes',
                'required',
                'string',
            ],
            'start_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'estimated_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'sometimes',
                'after_or_equal:start_date',
            ],
            'signed_document' => [
                'nullable',
                'file',
                'mimes:pdf',
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::enum(ServiceOrderStatusEnum::class),
            ],
            'supervisor_id' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],
            'supplier_id' => [
                'sometimes',
                'integer',
                'exists:suppliers,id',
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
        } elseif ($this->has('estimated_end_date') && $this->input('estimated_end_date') === null) {
             $this->merge(['estimated_end_date' => null]);
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
        });
    }
}