<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlaceRequest extends FormRequest
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
            'name' => [
            'required','string','max:255',
            Rule::unique('places', 'name')
                ->where(fn ($q) => $q->where('province_id', $this->input('province_id'))),],
            'type' => 'required|string|max:255|in:Centre de formation externe,Gratuit,Payant,Salle de formation ZA',
            'status' => 'required|string|max:255|in:Actif,Annulé,En pause,Clôturé',
            'capacity' => 'required|integer|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
        ];
    }
}
