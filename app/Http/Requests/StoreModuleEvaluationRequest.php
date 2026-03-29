<?php

namespace App\Http\Requests;

use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use App\Models\ModuleEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy: ModuleEvaluationPolicy@create
        return $this->user()?->can('create', ModuleEvaluation::class) ?? false;
    }

    public function rules(): array
    {
        // Build enum lists (string-backed enums)
        $statusValues = array_map(fn ($c) => $c->value, ModuleEvaluationStatus::cases());
        $typeValues   = array_map(fn ($c) => $c->value, ModuleEvaluationType::cases());

        return [
            // Foreign keys
            'module_id'      => ['required', 'integer', 'exists:modules,id'],
            'participant_id' => [
                'required',
                'integer',
                'exists:participants,id',
                // Composite uniqueness: (module_id, participant_id)
                Rule::unique('module_evaluations', 'participant_id')
                    ->where(fn ($q) => $q->where('module_id', $this->input('module_id'))),
            ],
            'trainer_id'     => ['required', 'integer', 'exists:trainers,id'],

            // Dates
            'evaluated_at'   => ['required', 'date'],

            // Enums (string columns, validated via enums)
            'evaluation_type'=> ['required', Rule::in($typeValues)],
            'status'         => ['sometimes', 'string', Rule::in($statusValues)],

            // Optional FKs
            'competency_grid_id' => ['nullable', 'integer', 'exists:competency_grids,id'],

            // Scores & comments
            'score_value'    => ['nullable', 'numeric', 'min:0'],   // adjust max if you want a range (e.g., max:100)
            'score_label'    => ['nullable', 'string', 'max:255'],
            'trainer_comments' => ['nullable', 'string', 'max:10000'],

            // Attachments: support array OR a single file
            'attachments'      => ['nullable', 'array'],
            'attachments.*'    => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            // If someone sends a single "attachment" instead of "attachments[]"
            'attachment'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
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
     * Optionally normalize inputs after validation.
     * Here we unify a single "attachment" into the "attachments" array
     * so your controller/service can consistently read $request->file('attachments').
     */
    protected function passedValidation(): void
    {
        if ($this->hasFile('attachment') && !$this->hasFile('attachments')) {
            $this->merge([
                // This keeps the original file available at attachments[0]
                'attachments' => [$this->file('attachment')],
            ]);
        }
    }

    /**
     * Friendly attribute names (for error messages).
     */
    public function attributes(): array
    {
        return [
            'module_id'          => 'module',
            'participant_id'     => 'participant',
            'trainer_id'         => 'formateur',
            'evaluated_at'       => 'date d’évaluation',
            'evaluation_type'    => 'type d’évaluation',
            'competency_grid_id' => 'grille de compétences',
            'score_value'        => 'score',
            'score_label'        => 'libellé du score',
            'trainer_comments'   => 'commentaires du formateur',
            'attachments'        => 'pièces jointes',
            'attachment'         => 'pièce jointe',
        ];
    }
}
