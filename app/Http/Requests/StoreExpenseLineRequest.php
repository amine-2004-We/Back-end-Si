<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseLineRequest extends FormRequest
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
            'designation' => 'required|string|max:255',
            'type' => 'required|in:restauration,deplacement,hebergement,transport,autre',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'label' => 'nullable|string|max:255',
            'departure_id' => 'nullable|required_if:type,deplacement|exists:provinces,id',
            'arrival_id' => 'nullable|required_if:type,deplacement|exists:provinces,id',
            'transport_mode' => 'nullable|required_if:type,deplacement|in:train,taxi,avion,voiture,bus',
            'justification' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', 
            'justification_path' => 'nullable|string|max:500', 
            'amount_manager' => 'nullable|numeric|min:0',
            'amount_finance' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'designation.required' => 'La désignation est obligatoire',
            'type.required' => 'Le type de frais est obligatoire',
            'type.in' => 'Le type de frais n\'est pas valide',
            'date.required' => 'La date est obligatoire',
            'amount.required' => 'Le montant est obligatoire',
            'amount.min' => 'Le montant doit être supérieur à 0',
            
            'departure_id.required_if' => 'Le lieu de départ est requis pour les déplacements',
            'arrival_id.required_if' => 'Le lieu d\'arrivée est requis pour les déplacements',
            'transport_mode.required_if' => 'Le mode de transport est requis pour les déplacements',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->type !== 'deplacement') {
            $this->merge([
                'departure_id' => null,
                'arrival_id' => null,
                'transport_mode' => null,
            ]);
        }
        
        // Map alternative field names to database field names
        if ($this->has('manager_amount') && !$this->has('amount_manager')) {
            $this->merge(['amount_manager' => $this->manager_amount]);
        }
        if ($this->has('finance_amount') && !$this->has('amount_finance')) {
            $this->merge(['amount_finance' => $this->finance_amount]);
        }
    }
}