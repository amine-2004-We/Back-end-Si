<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\FinalAcceptanceStatusEnum;
use App\Models\ProvisionalAcceptance;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Validation\Validator; // <-- 1. Assurez-vous d'importer Validator
use Carbon\Carbon; // <-- 2. Assurez-vous d'importer Carbon

class StoreFinalAcceptanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $provisionalAcceptanceRule = Rule::exists('provisional_acceptances', 'id')
                                         ->where('status', 'Validé')
                                         ->withoutTrashed();

        return [
            'provisional_acceptance_ids' => ['required', 'array', 'min:1'],
            'provisional_acceptance_ids.*' => ['required', 'integer', $provisionalAcceptanceRule],
            'calltender_id' => ['required', 'integer', Rule::exists('calltenders', 'id')->withoutTrashed()],
            'final_acceptance_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(FinalAcceptanceStatusEnum::class)],
            'auto_close_contract' => ['nullable', 'boolean'],
            'observations' => ['nullable', 'string'],
            'committee_ids' => ['required', 'array', 'min:1'],
            'committee_ids.*' => ['required', 'integer', Rule::exists('users', 'id')],
            'article_ids' => ['required', 'array', 'min:1'],
            'article_ids.*' => ['required', 'integer'],
        ];
    }

    /**
     * AJOUTER CETTE MÉTHODE COMPLÈTE
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return voidn
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            // On récupère les données qui ont déjà passé les règles de base
            $data = $validator->validated();

            $provisionalAcceptanceIds = $data['provisional_acceptance_ids'] ?? [];
            $finalAcceptanceDateStr = $data['final_acceptance_date'] ?? null;
            $submittedArticleIds = $data['article_ids'] ?? [];

            // Si un des champs requis manque, on ne continue pas
            if (empty($provisionalAcceptanceIds) || !$finalAcceptanceDateStr || empty($submittedArticleIds)) {
                return;
            }

            // --- 1. Validation de la Date (pour chaque PV) ---
            foreach ($provisionalAcceptanceIds as $provisionalAcceptanceId) {
                $pv = ProvisionalAcceptance::find($provisionalAcceptanceId);
                if (!$pv) {
                    continue;
                }
                if ($pv->provisional_acceptance_date) {
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

            // --- 2. Validation des Articles (doivent appartenir à au moins un PV sélectionné) ---
            $allValidArticleIds = DB::table('provisional_acceptance_items')
                ->whereIn('provisional_acceptance_id', $provisionalAcceptanceIds)
                ->pluck('article_id')
                ->unique()
                ->all();

            $invalidArticles = array_diff($submittedArticleIds, $allValidArticleIds);
            if (!empty($invalidArticles)) {
                $validator->errors()->add(
                    'article_ids',
                    'Un ou plusieurs articles ne font pas partie des PVs provisoires liés.'
                );
            }
        });
    }
}
