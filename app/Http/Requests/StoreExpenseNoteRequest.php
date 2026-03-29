<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Training;
use App\Models\BudgetLine;
use App\Models\Collaborator;
use App\Models\Trainer;
use App\Models\Participant;

class StoreExpenseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // rien
    }

    public function rules(): array
    {
        return [
            'code'               => ['prohibited'],
            'created_by_id'      => ['prohibited'],
            'validation_status'  => ['prohibited'],

            // alias OU FQCN
            'beneficiary_type'   => ['required', Rule::in([
                'collaborator','trainer','participant',
                Collaborator::class, Trainer::class, Participant::class,
            ])],
            'beneficiary_id'     => ['required','integer'],

            'training_id'        => ['required','integer', Rule::exists((new Training)->getTable(), 'id')],
            'budget_line_id'     => ['required','integer', Rule::exists((new BudgetLine)->getTable(), 'id')],

            'expense_date'       => ['required','date'],
            'expense_nature'     => ['required', Rule::in(['transport','lodging','meals','misc'])],
            'amount_ttc'         => ['required','numeric','min:0'],

            'attachment'         => ['sometimes','nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],
            'comments'           => ['nullable','string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('beneficiary_type');
            $id   = $this->input('beneficiary_id');

            if ($type && $id) {
                $class = match ($type) {
                    'collaborator', Collaborator::class => Collaborator::class,
                    'trainer',      Trainer::class      => Trainer::class,
                    'participant',  Participant::class  => Participant::class,
                    default => null,
                };

                $exists = $class ? $class::whereKey($id)->exists() : false;

                if (!$exists) {
                    $v->errors()->add('beneficiary_id', 'Le bénéficiaire spécifié est introuvable pour le type sélectionné.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'beneficiary_type' => 'type de bénéficiaire',
            'beneficiary_id'   => 'bénéficiaire',
            'training_id'      => 'formation',
            'budget_line_id'   => 'ligne budgétaire',
            'expense_date'     => 'date de dépense',
            'expense_nature'   => 'nature de la dépense',
            'amount_ttc'       => 'montant TTC',
            'attachment'       => 'pièce jointe',
            'comments'         => 'commentaires',
        ];
    }

    public function messages(): array
    {
        return [
            'code.prohibited'               => "Le champ code n'est pas autorisé.",
            'created_by_id.prohibited'      => "Le créateur est défini côté serveur.",
            'validation_status.prohibited'  => "Le statut n'est pas modifiable à la création (par défaut: draft).",

            'beneficiary_type.required'     => 'Le :attribute est requis.',
            'beneficiary_type.in'           => 'Le :attribute doit être un alias (collaborator, trainer, participant) ou un FQCN autorisé.',
            'beneficiary_id.required'       => 'Le :attribute est requis.',
            'beneficiary_id.integer'        => 'Le :attribute doit être un identifiant numérique.',

            'training_id.required'          => 'La :attribute est requise.',
            'training_id.integer'           => 'La :attribute doit être un identifiant numérique.',
            'training_id.exists'            => 'La :attribute est introuvable.',

            'budget_line_id.required'       => 'La :attribute est requise.',
            'budget_line_id.integer'        => 'La :attribute doit être un identifiant numérique.',
            'budget_line_id.exists'         => 'La :attribute est introuvable.',

            'expense_date.required'         => 'La :attribute est requise.',
            'expense_date.date'             => 'La :attribute doit être une date valide (YYYY-MM-DD).',

            'expense_nature.required'       => 'La :attribute est requise.',
            'expense_nature.in'             => 'La :attribute doit être l’une des valeurs: transport, lodging, meals, misc.',

            'amount_ttc.required'           => 'Le :attribute est requis.',
            'amount_ttc.numeric'            => 'Le :attribute doit être numérique.',
            'amount_ttc.min'                => 'Le :attribute doit être ≥ 0.',

            'attachment.file'               => 'La :attribute doit être un fichier.',
            'attachment.mimes'              => 'La :attribute doit être de type pdf, jpg, jpeg ou png.',
            'attachment.max'                => 'La :attribute ne doit pas dépasser 5 Mo.',

            'comments.string'               => 'Les :attribute doivent être une chaîne de caractères.',
        ];
    }
}
