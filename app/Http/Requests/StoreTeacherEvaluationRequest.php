<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherEvaluationRequest extends FormRequest
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
        'teacher_id'=>'required|exists:collaborators,id',
        'unit_id'=>'required|exists:units,id',
        'program_id'=>'required|exists:programs,id',
        'program_type_id'=>'required|exists:program_types,id',
        'general_appearance'=>'required|integer|min:0|max:10',
        'cleanliness'=>'required|integer|min:0|max:10',
        'punctuality'=>'required|integer|min:0|max:10',
        'respect_session_schedule'=>'required|integer|min:0|max:10',
        'preparation'=>'required|integer|min:0|max:10',
        'relationship_with_beneficiaries'=>'required|integer|min:0|max:10',
        'treatment_of_objectives'=>'required|integer|min:0|max:10',
        'assessment'=>'required|integer|min:0|max:10',
        'innovation'=>'required|integer|min:0|max:10',
        'pedagogical_approach'=>'required|integer|min:0|max:10',
        'participation'=>'required|integer|min:0|max:10',
        'error_correction'=>'required|integer|min:0|max:10',
        'comprehension'=>'required|integer|min:0|max:10',
        'knowledge_ritualization'=>'required|integer|min:0|max:10',
        'total_score'=>'required|numeric|min:0|max:150',
        'percentage'=>'required|numeric|min:0|max:100',
        'low_score_reason'=>'nullable|string',
        'observation'=>'nullable|string',

       ];
    }
}
