<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Training;
use App\Models\BudgetLine;
use App\Models\Collaborator;
use App\Models\Trainer;
use App\Models\Participant;

class UpdateExpenseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'code'               => ['prohibited'],
            'created_by_id'      => ['prohibited'],
            'validation_status'  => ['prohibited'],

            'beneficiary_type'   => ['sometimes', Rule::in([
                'collaborator','trainer','participant',
                Collaborator::class, Trainer::class, Participant::class,
            ]), 'required_with:beneficiary_id'],
            'beneficiary_id'     => ['sometimes', 'integer', 'required_with:beneficiary_type'],

            'training_id'        => ['sometimes','integer', Rule::exists((new Training)->getTable(), 'id')],
            'budget_line_id'     => ['sometimes','integer', Rule::exists((new BudgetLine)->getTable(), 'id')],

            'expense_date'       => ['sometimes','date'],
            'expense_nature'     => ['sometimes', Rule::in(['transport','lodging','meals','misc'])],
            'amount_ttc'         => ['sometimes','numeric','min:0'],

            'attachment'         => ['sometimes','nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],
            'attachment_remove'  => ['sometimes','boolean'],

            'comments'           => ['sometimes','nullable','string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $typeProvided = $this->filled('beneficiary_type');
            $idProvided   = $this->filled('beneficiary_id');

            if ($typeProvided && $idProvided) {
                $type = $this->input('beneficiary_type');
                $id   = (int) $this->input('beneficiary_id');

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
            'attachment_remove'=> 'suppression de la pièce jointe',
            'comments'         => 'commentaires',
        ];
    }

    public function messages(): array
    {
        return [
            'code.prohibited'               => "Le champ code n'est pas modifiable.",
            'created_by_id.prohibited'      => "Le créateur n'est pas modifiable ici.",
            'validation_status.prohibited'  => "Le statut se met à jour via l'endpoint dédié.",

            'beneficiary_type.in'           => 'Le type de bénéficiaire doit être un alias (collaborator, trainer, participant) ou un FQCN autorisé.',
            'beneficiary_type.required_with'=> 'Le type de bénéficiaire est requis lorsque le bénéficiaire est fourni.',
            'beneficiary_id.required_with'  => 'Le bénéficiaire est requis lorsque le type de bénéficiaire est fourni.',
            'beneficiary_id.integer'        => 'Le bénéficiaire doit être un identifiant numérique.',

            'training_id.integer'           => 'La formation doit être un identifiant numérique.',
            'training_id.exists'            => 'La formation est introuvable.',

            'budget_line_id.integer'        => 'La ligne budgétaire doit être un identifiant numérique.',
            'budget_line_id.exists'         => 'La ligne budgétaire est introuvable.',

            'expense_date.date'             => 'La date de dépense doit être une date valide (YYYY-MM-DD).',
            'expense_nature.in'             => 'La nature doit être: transport, lodging, meals, misc.',
            'amount_ttc.numeric'            => 'Le montant TTC doit être numérique.',
            'amount_ttc.min'                => 'Le montant TTC doit être ≥ 0.',

            'attachment.file'               => 'La pièce jointe doit être un fichier.',
            'attachment.mimes'              => 'La pièce jointe doit être de type pdf, jpg, jpeg ou png.',
            'attachment.max'                => 'La pièce jointe ne doit pas dépasser 5 Mo.',
            'attachment_remove.boolean'     => 'Le champ de suppression doit être booléen.',

            'comments.string'               => 'Les commentaires doivent être une chaîne de caractères.',
        ];
    }
}
