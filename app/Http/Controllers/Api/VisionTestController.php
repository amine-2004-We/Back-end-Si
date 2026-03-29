<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VisionTestService;
use App\Http\Requests\UpsertVisionTestRequest;
use Exception;

class VisionTestController extends Controller
{
    protected $visionTestService;

    public function __construct(VisionTestService $visionTestService)
    {
        $this->visionTestService = $visionTestService;
    }

    /**
     * Affiche les informations d'un test de vision pour un bénéficiaire.
     *
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $beneficiaryId)
    {
        /*try {
            $visionTest = $this->visionTestService->getVisionTest($beneficiaryId);

            if (!$visionTest) {
                return response()->json(['message' => 'Vision test non trouvé'], 404);
            }

            return response()->json($visionTest);

        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération du test de vision.'], 500);
        }*/
        $response = null;

        try {
            $visionTest = $this->visionTestService->getVisionTest($beneficiaryId);

            if (!$visionTest) {
                $response = response()->json(['message' => 'Vision test non trouvé'], 404);
            } else {
                $response = response()->json($visionTest);
            }
        } catch (Exception $e) {
            $response = response()->json(['message' => 'Une erreur est survenue lors de la récupération du test de vision.'], 500);
        }

        return $response;
    }

    /**
     * Crée ou met à jour les informations d'un test de vision.
     *
     * @param \App\Http\Requests\UpsertVisionTestRequest $request La requête de validation des données.
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upsert(UpsertVisionTestRequest $request, int $beneficiaryId)
    {
        /*try {
            $validated = $request->validated();
            $vision = $this->visionTestService->saveVisionTest($beneficiaryId, $validated);

            return response()->json($vision, 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de l\'enregistrement du test de vision.'], 500);
        }*/
        $response = null;

        try {
            $validated = $request->validated();
            $vision = $this->visionTestService->saveVisionTest($beneficiaryId, $validated);
            
            $response = response()->json($vision, 200);
        } catch (Exception $e) {
            $response = response()->json(['message' => 'Une erreur est survenue lors de l\'enregistrement du test de vision.'], 500);
        }

        return $response;
    }
}
