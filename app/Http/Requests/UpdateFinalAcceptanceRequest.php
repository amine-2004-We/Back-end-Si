<?php

namespace App\Http\Requests;

use App\Enums\FinalAcceptanceStatusEnum;
use App\Models\ProvisionalAcceptance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\FinalAcceptance;
use Illuminate\Validation\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- 1. Assurez-vous que DB est importé
// use App\Rules\ArticlesInProvisionalAcceptance; // <-- 2. SUPPRIMEZ CETTE LIGNE

class UpdateFinalAcceptanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provisional_acceptance_id' => ['prohibited'],
            'calltender_id' => ['prohibited'],

            // Ajout de la gestion des PVs provisoires liés
            'provisional_acceptance_ids' => ['sometimes', 'array', 'min:1'],
            'provisional_acceptance_ids.*' => ['required', 'integer', Rule::exists('provisional_acceptances', 'id')->where('status', 'Validé')->withoutTrashed()],

            'final_acceptance_date' => ['sometimes', 'required', 'date_format:Y-m-d'],

            'status' => ['sometimes', 'required', Rule::enum(FinalAcceptanceStatusEnum::class)],
            'auto_close_contract' => ['sometimes', 'nullable', 'boolean'],
            'observations' => ['sometimes', 'nullable', 'string'],

            'committee_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'committee_ids.*' => ['required', 'integer', Rule::exists('users', 'id')],

            'article_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'article_ids.*' => ['required', 'integer'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $finalAcceptance = FinalAcceptance::find($this->route('id'));
            if (!$finalAcceptance) {
                return; // Le contrôleur renverra 404
            }

            // On prend les PVs envoyés dans la requête si présents, sinon ceux déjà liés
            $pvIds = $this->provisional_acceptance_ids ?? $finalAcceptance->provisionalAcceptances->pluck('id')->toArray();
            $provisionalAcceptances = ProvisionalAcceptance::whereIn('id', $pvIds)->get();

            // --- 1. Validation de la Date (si elle est envoyée) ---
            $finalAcceptanceDateStr = $this->final_acceptance_date;
            if ($finalAcceptanceDateStr && $provisionalAcceptances->count() > 0) {
                if ($validator->errors()->has('final_acceptance_date')) {
                    return;
                }
                foreach ($provisionalAcceptances as $pv) {
                    $provisionalDate = $pv->provisional_acceptance_date;
                    $finalAcceptanceDate = Carbon::parse($finalAcceptanceDateStr);
                    if ($finalAcceptanceDate->lt($provisionalDate)) {
                        $validator->errors()->add(
                            'final_acceptance_date',
                            'La date définitive doit être après ou égale à la date provisoire (' . $provisionalDate->format('d/m/Y') . ').'
                        );
                    }
                }
            }

            // --- 2. Validation des Articles (si elle est envoyée) ---
            $submittedArticleIds = $this->article_ids;
            if (!empty($submittedArticleIds) && $provisionalAcceptances->count() > 0) {
                if ($validator->errors()->has('article_ids') || $validator->errors()->has('article_ids.*')) {
                    return;
                }
                $allValidArticleIds = [];
                foreach ($provisionalAcceptances as $pv) {
                    $validArticleIds = DB::table('provisional_acceptance_items')
                        ->where('provisional_acceptance_id', $pv->id)
                        ->pluck('article_id')
                        ->all();
                    $allValidArticleIds = array_merge($allValidArticleIds, $validArticleIds);
                }
                $allValidArticleIds = array_unique($allValidArticleIds);
                $invalidArticles = array_diff($submittedArticleIds, $allValidArticleIds);
                if (!empty($invalidArticles)) {
                    $validator->errors()->add(
                        'article_ids',
                        'Un ou plusieurs articles ne font pas partie des PV provisoires liés.'
                    );
                }
            }
        });
    }
}
