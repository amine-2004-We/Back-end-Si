<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallForProjectRequest extends FormRequest
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
             'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'debut_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'estimated_budget' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|in:Ouvert,Accepté,Refusé,Clôturé,',
            'registration_link' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|string',
            'selection_criteria' => 'sometimes|required|string',
            'required_documents' => 'sometimes|nullable|array',
            
           
            'sponsor_id' => 'sometimes|nullable|exists:partners,id',
            'submission_date' => 'sometimes|required|date',
            'submission_indicators' => 'sometimes|nullable|string',
            'expertise_areas' => 'sometimes|nullable|string',
            'target_audience' => 'sometimes|required|string',
            'project_duration' => 'sometimes|required|integer',
            'potential_profiles' => 'sometimes|nullable|string',
            'required_resources' => 'sometimes|nullable|string',
            'documents_to_delete' => 'sometimes|nullable',
           
        ];
    }
}
