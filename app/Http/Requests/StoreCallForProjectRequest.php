<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallForProjectRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'debut_date' => 'required|date',
            'end_date' => 'required|date',
            'estimated_budget' => 'required|numeric',
            'status' => 'required|string|in:Ouvert,Accepté,Refusé,Clôturé,',
            'registration_link' => 'nullable|string',
            'type' => 'nullable|string',
            'selection_criteria' => 'required|string',
            'required_documents' => 'nullable|array',
            // 'required_documents.*' => 'nullable|string',
         
            'sponsor_id' => 'nullable|exists:partners,id',
            'submission_date' => 'required|date',
            'submission_indicators' => 'nullable|string',
            'expertise_areas' => 'nullable|string',
            'target_audience' => 'required|string',
            'project_duration' => 'required|integer',
            'potential_profiles' => 'nullable|string',
            'required_resources' => 'nullable|string',
            'responsible_id' => 'nullable|exists:collaborators,id',
            'work_plan' => 'nullable|string',
            

        ];
    }

    public function messages(): array
    {
        return [
            'sponsor_id.exists' => 'le Commanditaire sélectionné est invalide.',
            'responsible_id.exists' => 'le Responsable sélectionné est invalide.',
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'debut_date.required' => 'La date de début est obligatoire.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'estimated_budget.required' => 'Le budget estimé est obligatoire.',
            'selection_criteria.required' => 'Les critères de sélection sont obligatoires.',
            'submission_date.required' => 'La date de soumission est obligatoire.',
            'target_audience.required' => "Le public cible est obligatoire.",
            'project_duration.required' => 'La durée du projet est obligatoire.',

        ];
    }
}
