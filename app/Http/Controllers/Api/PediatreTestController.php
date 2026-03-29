<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PediatreTestService;
use App\Http\Requests\UpsertPediatreTestRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PediatreTestController extends Controller
{
    protected $pediatreTestService;

    public function __construct(PediatreTestService $pediatreTestService)
    {
        $this->pediatreTestService = $pediatreTestService;
    }

    /**
     * Affiche les informations d'un test pédiatrique pour un bénéficiaire.
     *
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $beneficiaryId)
    {
        /*try {
            $pediatreTest = $this->pediatreTestService->getPediatreTest($beneficiaryId);

            if (!$pediatreTest) {
                return response()->json(['message' => 'Test pédiatrique non trouvé'], 404);
            }

            return response()->json($pediatreTest);

        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération du test pédiatrique.'], 500);
        }*/
        try {
            $pediatreTest = $this->pediatreTestService->getPediatreTest($beneficiaryId);

            if (!$pediatreTest) {
                throw new ModelNotFoundException("Test pédiatrique non trouvé");
            }

            return response()->json($pediatreTest);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération du test pédiatrique.'], 500);
        }
    }

    /**
     * Crée ou met à jour les informations d'un test pédiatrique.
     *
     * @param \App\Http\Requests\UpsertPediatreTestRequest $request La requête de validation des données.
     * @param int $beneficiaryId L'identifiant du bénéficiaire.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upsert(UpsertPediatreTestRequest $request, int $beneficiaryId)
    {
        try {
            $validated = $request->validated();
            $pediatreTest = $this->pediatreTestService->savePediatreTest($beneficiaryId, $validated);

            return response()->json($pediatreTest, 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement du test pédiatrique.'
            ], 500);
        }
    }
}
