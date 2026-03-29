<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UnitTypeEnum;
use App\Enums\UnitStatusEnum;
use App\UnitStatusEnum as AppUnitStatusEnum;
use App\UnitTypeEnum as AppUnitTypeEnum;

/**
 * Handles validation for storing a new Unit.
 */
class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Implement your authorization logic here.
        // For now, allowing all authenticated users.
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
            'name' => ['required', 'string', 'max:255'],
            'partner_code' => ['required', 'string', 'max:255', Rule::unique('units', 'partner_code')],
            'site_id' => ['required', 'exists:sites,id'],
            'type' => ['required', Rule::in(array_column(AppUnitTypeEnum::cases(), 'value'))],
            'number_of_classes' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_column(AppUnitStatusEnum::cases(), 'value'))],
            'educator_id' => ['nullable', 'exists:users,id'],
            'observations' => ['nullable', 'string'],
            'classes' => 'nullable|array',
            'classes.*.class_name' => 'required|string|max:255',
            'classes.*.levels_id' => 'required|integer|exists:levels,id',
            'classes.*.cycle_id' => 'required|integer|exists:cycles,id',
            'classes.*.class_type_id' => 'required|integer|exists:class_types,id',
            'classes.*.activity_leader' => 'required|integer|exists:collaborators,id',
            'classes.*.local_pedagogical_coordinator' => 'nullable|integer|exists:collaborators,id',
            'classes.*.class_status_id' => 'required|integer|exists:class_status,id',
            'classes.*.start_date' => 'required|date',
            'classes.*.end_date' => 'required|date|after_or_equal:classes.*.start_date',
            'classes.*.note' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'unité est obligatoire.',
            'site_id.required' => 'Le site d\'appartenance est obligatoire.',
            'site_id.exists' => 'Le site sélectionné n\'existe pas.',
            'type.required' => 'Le type d\'unité est obligatoire.',
            'type.in' => 'Le type d\'unité sélectionné est invalide.',
            'number_of_classes.required' => 'Le nombre de classes est obligatoire.',
            'number_of_classes.integer' => 'Le nombre de classes doit être un entier.',
            'number_of_classes.min' => 'Le nombre de classes ne peut pas être négatif.',
            'status.required' => 'Le statut de l\'unité est obligatoire.',
            'status.in' => 'Le statut de l\'unité sélectionné est invalide.',
            'educator_id.exists' => 'L\'éducatrice sélectionnée n\'existe pas.',
            'classes.*.class_name.required' => 'Le nom de la classe est requis pour toutes les classes.',
            'classes.*.levels_id.required' => 'Le niveau est requis pour toutes les classes.',
            'classes.*.cycle_id.required' => 'Le cycle est requis pour toutes les classes.',
            'classes.*.class_type_id.required' => 'Le type de classe est requis pour toutes les classes.',
            'classes.*.activity_leader.required' => 'Le responsable d\'activité est requis pour toutes les classes.',
            'classes.*.class_status_id.required' => 'Le statut est requis pour toutes les classes.',
            'classes.*.start_date.required' => 'La date de début est requise pour toutes les classes.',
            'classes.*.end_date.required' => 'La date de fin est requise pour toutes les classes.',
        ];
    }
}

