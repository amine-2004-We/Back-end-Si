<?php

namespace App\Http\Requests;

use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use App\Models\ModuleEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ModuleEvaluation|null $model */
        $model = $this->route('moduleEvaluation')
            ?? $this->route('module_evaluation');

        return $this->user()?->can('update', $model ?? ModuleEvaluation::class) ?? false;
    }

    public function rules(): array
    {
        $statusValues = array_map(fn ($c) => $c->value, ModuleEvaluationStatus::cases());
        $typeValues   = array_map(fn ($c) => $c->value, ModuleEvaluationType::cases());

        $model = $this->route('moduleEvaluation')
            ?? $this->route('module_evaluation');

        // Current values (used for composite-unique fallback)
        $currentId          = (int) optional($model)->id;
        $currentModuleId    = (int) optional($model)->module_id;
        $moduleIdForUnique  = (int) ($this->input('module_id') ?? $currentModuleId);

        return [
            // Foreign keys (optional updates)
            'module_id'      => ['sometimes', 'integer', 'exists:modules,id'],
            'participant_id' => [
                'sometimes',
                'integer',
                'exists:participants,id',
                // keep composite uniqueness, ignoring this row
                Rule::unique('module_evaluations', 'participant_id')
                    ->where(fn ($q) => $q->where('module_id', $moduleIdForUnique))
                    ->ignore($currentId),
            ],
            'trainer_id'     => ['sometimes', 'integer', 'exists:trainers,id'],

            // Dates
            'evaluated_at'   => ['sometimes', 'date'],

            // Enums (string columns, validated via enums)
            'evaluation_type'=> ['sometimes', Rule::in($typeValues)],
            'status'         => ['sometimes', 'string', Rule::in($statusValues)],

            // Optional FKs
            'competency_grid_id' => ['sometimes', 'nullable', 'integer', 'exists:competency_grids,id'],

            // Scores & comments
            'score_value'      => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'score_label'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'trainer_comments' => ['sometimes', 'nullable', 'string', 'max:10000'],

            // Attachments: support array OR a single file
            'attachments'      => ['sometimes', 'array'],
            'attachments.*'    => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            // Single upload alternative
            'attachment'       => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            // Optional list of existing attachment paths/keys to remove
            'attachments_remove'   => ['sometimes', 'array'],
            'attachments_remove.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'participant_id.unique' => 'Ce participant possède déjà une évaluation pour ce module.',
            'attachments.*.mimes'   => 'Les pièces jointes doivent être au format PDF, JPG, JPEG ou PNG.',
            'attachments.*.max'     => 'Chaque pièce jointe ne doit pas dépasser 10 Mo.',
            'attachment.mimes'      => 'La pièce jointe doit être au format PDF, JPG, JPEG ou PNG.',
            'attachment.max'        => 'La pièce jointe ne doit pas dépasser 10 Mo.',
        ];
    }

    /**
     * Normalize inputs after validation:
     * - If a single "attachment" is sent, move it into "attachments" for consistency.
     */
    protected function passedValidation(): void
    {
        if ($this->hasFile('attachment')) {
            $merged = (array) $this->file('attachments');
            $merged[] = $this->file('attachment');

            $this->merge([
                'attachments' => $merged,
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'module_id'           => 'module',
            'participant_id'      => 'participant',
            'trainer_id'          => 'formateur',
            'evaluated_at'        => 'date d’évaluation',
            'evaluation_type'     => 'type d’évaluation',
            'competency_grid_id'  => 'grille de compétences',
            'score_value'         => 'score',
            'score_label'         => 'libellé du score',
            'trainer_comments'    => 'commentaires du formateur',
            'attachments'         => 'pièces jointes',
            'attachment'          => 'pièce jointe',
            'attachments_remove'  => 'pièces jointes à supprimer',
        ];
    }
}
