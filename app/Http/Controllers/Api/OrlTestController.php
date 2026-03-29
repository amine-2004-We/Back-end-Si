<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrlTestService;
use App\Http\Requests\UpsertOrlTestRequest;
use Exception;

class OrlTestController extends Controller
{
    protected $orlTestService;

    public function __construct(OrlTestService $orlTestService)
    {
        $this->orlTestService = $orlTestService;
    }

    /**
     * Affiche les informations d'un test ORL pour un bénéficiaire.
     * * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $beneficiaryId)
    {
        $response = null;

        try {
            $orl = $this->orlTestService->getOrlTest($beneficiaryId);

            if (!$orl) {
                $response = response()->json(['message' => 'Test ORL non trouvé'], 404);
            } else {
                $response = response()->json($orl);
            }
        } catch (Exception $e) {
            $response = response()->json(['message' => 'Une erreur est survenue lors de la récupération du test ORL.'], 500);
        }

        return $response;
    }

    /**
     * Crée ou met à jour les informations d'un test ORL.
     * * @param \App\Http\Requests\UpsertOrlTestRequest $request La requête de validation des données.
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponsen
     */
    public function upsert(UpsertOrlTestRequest $request, int $beneficiaryId)
    {
        $response = null;

        try {
            $validated = $request->validated();
            $orl = $this->orlTestService->saveOrlTest($beneficiaryId, $validated);

            $response = response()->json($orl, 200);
        } catch (Exception $e) {
            $response = response()->json(['message' => 'Une erreur est survenue lors de l\'enregistrement du test ORL.'], 500);
        }

        return $response;
    }
}
