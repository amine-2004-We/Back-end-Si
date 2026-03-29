<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherEvaluationRequest extends FormRequest
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

            'teacher_id'=>'sometimes|required|exists:collaborators,id',
            'unit_id'=>'sometimes|required|exists:units,id',
            'programme_id'=>'sometimes|required|exists:programmes,id',
            'program_type_id'=>'sometimes|required|exists:program_types,id',
            'general_appearance'=>'sometimes|required|integer|min:0|max:10',
            'cleanliness'=>'sometimes|required|integer|min:0|max:10',
            'punctuality'=>'sometimes|required|integer|min:0|max:10',
            'respect_session_schedule'=>'sometimes|required|integer|min:0|max:10',
            'preparation'=>'sometimes|required|integer|min:0|max:10',
            'relationship_with_beneficiaries'=>'sometimes|required|integer|min:0|max:10',
            'treatment_of_objectives'=>'sometimes|required|integer|min:0|max:10',
            'assessment'=>'sometimes|required|integer|min:0|max:10',
            'innovation'=>'sometimes|required|integer|min:0|max:10',
            'pedagogical_approach'=>'sometimes|required|integer|min:0|max:10',
            'participation'=>'sometimes|required|integer|min:0|max:10',
            'error_correction'=>'sometimes|required|integer|min:0|max:10',
            'comprehension'=>'sometimes|required|integer|min:0|max:10',
            'knowledge_ritualization'=>'sometimes|required|integer|min:0|max:10',
            'total_score'=>'sometimes|required|numeric|min:0|max:150',
            'percentage'=>'sometimes|required|numeric|min:0|max:100',
            'low_score_reason'=>'nullable|string',
            'observation'=>'nullable|string',

        ];
    }
}
