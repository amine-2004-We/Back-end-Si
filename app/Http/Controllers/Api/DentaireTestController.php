<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DentaireTestService;
use App\Http\Requests\UpsertDentaireTestRequest;
use Exception;

class DentaireTestController extends Controller
{
    protected $dentaireTestService;

    public function __construct(DentaireTestService $dentaireTestService)
    {
        $this->dentaireTestService = $dentaireTestService;
    }

    /**
     * Affiche les informations d'un test dentaire pour un bénéficiaire.
     *
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $beneficiaryId)
    {
        try {
            $dentaire = $this->dentaireTestService->getDentaireTest($beneficiaryId);

            if (!$dentaire) {
                return response()->json(['message' => 'Test dentaire non trouvé'], 404);
            }

            return response()->json($dentaire);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération du test dentaire.'
            ], 500);
        }
    }

    /**
     * Crée ou met à jour les informations d'un test dentaire.
     *
     * @param \App\Http\Requests\UpsertDentaireTestRequest $request La requête de validation des données.
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upsert(UpsertDentaireTestRequest $request, int $beneficiaryId)
    {
        try {
            $validated = $request->validated();
            $dentaire = $this->dentaireTestService->saveDentaireTest($beneficiaryId, $validated);

            return response()->json($dentaire, 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement du test dentaire.'
            ], 500);
        }
    }
}
