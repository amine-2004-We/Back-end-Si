<?php

namespace App\Http\Requests;

use App\Enums\AtelierE2CNGEnum;
use App\Enums\MatiereE2CNGEnum;
use App\Enums\ProgrammeTypeEnum;
use App\Enums\ProfessionalOptionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProgrammePedagogiqueE2CNGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'groupe_id' => ['nullable', 'exists:groups,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'type' => ['sometimes', new Enum(ProgrammeTypeEnum::class)],
            'date_prevue' => ['sometimes', 'date'],

            'project_pedagogique' => ['nullable', new Enum(MatiereE2CNGEnum::class)],
            'professional_option' => ['nullable', new Enum(ProfessionalOptionEnum::class)],
            
            'metier' => ['nullable', new Enum(AtelierE2CNGEnum::class)],
            'ateliers' => ['nullable', new Enum(AtelierE2CNGEnum::class)],

            'project_pedagogique_arabe' => ['nullable', 'string', 'max:255'],
            'metier_arabe' => ['nullable', 'string', 'max:255'],
            'ateliers_arabe' => ['nullable', 'array'],
            'observation' => ['nullable', 'string'],
            'date_prevu' => ['nullable', 'date'],
            'date_realisation' => ['nullable', 'date'],
            'real_start_date' => ['nullable', 'date'],
            'real_end_date' => ['nullable', 'date', 'after_or_equal:real_start_date'],
        ];
    }
}